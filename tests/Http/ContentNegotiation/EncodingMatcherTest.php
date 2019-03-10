<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/net/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http\Formatting;

use Aphiria\Net\Http\ContentNegotiation\EncodingMatcher;
use Aphiria\Net\Http\ContentNegotiation\MediaTypeFormatters\IMediaTypeFormatter;
use Aphiria\Net\Http\Headers\AcceptCharsetHeaderValue;
use Aphiria\Net\Http\Headers\AcceptMediaTypeHeaderValue;
use Aphiria\Net\Http\Headers\ContentTypeHeaderValue;
use Opulence\Collections\ImmutableHashTable;
use Opulence\Collections\KeyValuePair;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the encoding matcher
 */
class EncodingMatcherTest extends TestCase
{
    /** @var EncodingMatcher The matcher to use in tests */
    private $matcher;

    protected function setUp(): void
    {
        $this->matcher = new EncodingMatcher();
    }

    public function testBestEncodingCanMatchMismatchingCasesInAcceptCharset(): void
    {
        $formatter = $this->createFormatterMock(['utf-8']);
        $acceptCharsetHeader = new AcceptCharsetHeaderValue('UTF-8');
        $encoding = $this->matcher->getBestEncodingMatch($formatter, [$acceptCharsetHeader], null);
        $this->assertEquals('utf-8', $encoding);
    }

    public function testBestEncodingCanMatchMismatchingCasesInAcceptHeader(): void
    {
        $formatter = $this->createFormatterMock(['utf-8']);
        $acceptHeaderParameters = new ImmutableHashTable([new KeyValuePair('charset', 'UTF-8')]);
        $acceptHeader = new AcceptMediaTypeHeaderValue('text/html', $acceptHeaderParameters);
        $encoding = $this->matcher->getBestEncodingMatch($formatter, [], $acceptHeader);
        $this->assertEquals('utf-8', $encoding);
    }

    public function testBestEncodingComesFromAcceptCharsetHeaderIfItAndAcceptHeaderHaveSupportedEncodings(): void
    {
        $formatter = $this->createFormatterMock(['utf-8', 'utf-16']);
        $acceptHeaderParameters = new ImmutableHashTable([new KeyValuePair('charset', 'utf-8')]);
        $acceptHeader = new AcceptMediaTypeHeaderValue('text/html', $acceptHeaderParameters);
        $acceptCharsetHeader = new AcceptCharsetHeaderValue('utf-16');
        $encoding = $this->matcher->getBestEncodingMatch($formatter, [$acceptCharsetHeader], $acceptHeader);
        $this->assertEquals('utf-16', $encoding);
    }

    public function testBestEncodingComesFromAcceptCharsetHeaderIfNoAcceptHeaderIsPresent(): void
    {
        $formatter = $this->createFormatterMock(['utf-16']);
        $acceptCharsetHeader = new AcceptCharsetHeaderValue('utf-16');
        $encoding = $this->matcher->getBestEncodingMatch($formatter, [$acceptCharsetHeader], null);
        $this->assertEquals('utf-16', $encoding);
    }

    public function testBestEncodingComesFromAcceptHeaderIfAcceptCharsetHeaderIsNotPresent(): void
    {
        $formatter = $this->createFormatterMock(['utf-16']);
        $acceptHeaderParameters = new ImmutableHashTable([new KeyValuePair('charset', 'utf-16')]);
        $acceptHeader = new AcceptMediaTypeHeaderValue('text/html', $acceptHeaderParameters);
        $encoding = $this->matcher->getBestEncodingMatch($formatter, [], $acceptHeader);
        $this->assertEquals('utf-16', $encoding);
    }

    public function testBestEncodingComesFromWildcardInAcceptCharsetHeader(): void
    {
        $formatter = $this->createFormatterMock(['utf-8']);
        $acceptCharsetHeader = new AcceptCharsetHeaderValue('*');
        $encoding = $this->matcher->getBestEncodingMatch($formatter, [$acceptCharsetHeader], null);
        $this->assertEquals('utf-8', $encoding);
    }

    public function testBestEncodingComesFromWildcardInContentTypeHeader(): void
    {
        $formatter = $this->createFormatterMock(['utf-8']);
        $contentTypeHeader = new ContentTypeHeaderValue(
            'application/json',
            new ImmutableHashTable([new KeyValuePair('charset', '*')])
        );
        $encoding = $this->matcher->getBestEncodingMatch($formatter, [], $contentTypeHeader);
        $this->assertEquals('utf-8', $encoding);
    }

    public function testBestEncodingIsChosenInOrderOfQualityScore(): void
    {
        $formatter = $this->createFormatterMock(['utf-8', 'utf-16']);
        $acceptCharsetHeaders = [
            new AcceptCharsetHeaderValue('utf-8', new ImmutableHashTable([new KeyValuePair('q', 0.1)])),
            new AcceptCharsetHeaderValue('utf-16', new ImmutableHashTable([new KeyValuePair('q', 0.5)])),
        ];
        $encoding = $this->matcher->getBestEncodingMatch($formatter, $acceptCharsetHeaders, null);
        $this->assertEquals('utf-16', $encoding);
    }

    public function testBestEncodingIsNullWhenFormatterDoesNotSupportCharsetFromContentTypeHeader(): void
    {
        $formatter = $this->createFormatterMock(['utf-8']);
        $contentTypeHeaderParameters = new ImmutableHashTable([new KeyValuePair('charset', 'utf-16')]);
        $contentTypeHeader = new ContentTypeHeaderValue('text/html', $contentTypeHeaderParameters);
        $encoding = $this->matcher->getBestEncodingMatch($formatter, [], $contentTypeHeader);
        $this->assertNull($encoding);
    }

    public function testBestEncodingIsNullWhenFormatterDoesNotSupportCharsetFromAcceptCharsetHeader(): void
    {
        $formatter = $this->createFormatterMock(['utf-8']);
        $acceptCharsetHeader = new AcceptCharsetHeaderValue('utf-16');
        $encoding = $this->matcher->getBestEncodingMatch($formatter, [$acceptCharsetHeader], null);
        $this->assertNull($encoding);
    }

    public function testBestEncodingIsNullWhenFormatterDoesNotSupportCharsetFromAcceptHeader(): void
    {
        $formatter = $this->createFormatterMock(['utf-8']);
        $acceptHeaderParameters = new ImmutableHashTable([new KeyValuePair('charset', 'utf-16')]);
        $acceptHeader = new AcceptMediaTypeHeaderValue('text/html', $acceptHeaderParameters);
        $encoding = $this->matcher->getBestEncodingMatch($formatter, [], $acceptHeader);
        $this->assertNull($encoding);
    }

    public function testBestEncodingIsNullWhenMatchingZeroQualityScoreCharset(): void
    {
        /** @var IMediaTypeFormatter|MockObject $formatter */
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $acceptCharsetHeader = new AcceptCharsetHeaderValue(
            'utf-8',
            new ImmutableHashTable([new KeyValuePair('q', 0.0)])
        );
        $encoding = $this->matcher->getBestEncodingMatch($formatter, [$acceptCharsetHeader], null);
        $this->assertNull($encoding);
    }

    /**
     * Creates a mock media type formatter with a list of supported encodings
     *
     * @param array $supportedEncodings The list of supported encodings
     * @return IMediaTypeFormatter|MockObject The mocked formatter
     */
    private function createFormatterMock(array $supportedEncodings): IMediaTypeFormatter
    {
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $formatter->expects($this->once())
            ->method('getSupportedEncodings')
            ->willReturn($supportedEncodings);

        return $formatter;
    }
}
