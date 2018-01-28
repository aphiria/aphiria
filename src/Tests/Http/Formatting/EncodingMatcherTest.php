<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting;

use Opulence\Collections\ImmutableHashTable;
use Opulence\Collections\KeyValuePair;
use Opulence\Net\Http\Formatting\EncodingMatcher;
use Opulence\Net\Http\Formatting\IMediaTypeFormatter;
use Opulence\Net\Http\Headers\AcceptCharsetHeaderValue;
use Opulence\Net\Http\Headers\AcceptMediaTypeHeaderValue;
use Opulence\Net\Http\Headers\ContentTypeHeaderValue;

/**
 * Tests the encoding matcher
 */
class EncodingMatcherTest extends \PHPUnit\Framework\TestCase
{
    /** @var EncodingMatcher The matcher to use in tests */
    private $matcher;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->matcher = new EncodingMatcher();
    }

    /**
     * Tests that the best encoding comes from the Accept-Charset header if it and the Accept header have supported encodings
     */
    public function testBestEncodingComesFromAcceptCharsetHeaderIfItAndAcceptHeaderHaveSupportedEncodings() : void
    {
        $formatter = $this->createFormatterMock(['utf-8', 'utf-16']);
        $acceptHeaderParameters = new ImmutableHashTable([new KeyValuePair('charset', 'utf-8')]);
        $acceptHeader = new AcceptMediaTypeHeaderValue('text/html', $acceptHeaderParameters);
        $acceptCharsetHeader = new AcceptCharsetHeaderValue('utf-16');
        $encoding = $this->matcher->getBestEncodingMatch($formatter, [$acceptCharsetHeader], $acceptHeader);
        $this->assertEquals('utf-16', $encoding);
    }

    /**
     * Tests that the best encoding comes from the Accept-Charset header if the formatter supports it
     */
    public function testBestEncodingComesFromAcceptCharsetHeaderIfNoAcceptHeaderIsPresent() : void
    {
        $formatter = $this->createFormatterMock(['utf-16']);
        $acceptCharsetHeader = new AcceptCharsetHeaderValue('utf-16');
        $encoding = $this->matcher->getBestEncodingMatch($formatter, [$acceptCharsetHeader], null);
        $this->assertEquals('utf-16', $encoding);
    }

    /**
     * Tests that the best encoding comes from the Accept header if the Accept-Charset header is not present
     */
    public function testBestEncodingComesFromAcceptHeaderIfAcceptCharsetHeaderIsNotPresent() : void
    {
        $formatter = $this->createFormatterMock(['utf-16']);
        $acceptHeaderParameters = new ImmutableHashTable([new KeyValuePair('charset', 'utf-16')]);
        $acceptHeader = new AcceptMediaTypeHeaderValue('text/html', $acceptHeaderParameters);
        $encoding = $this->matcher->getBestEncodingMatch($formatter, [], $acceptHeader);
        $this->assertEquals('utf-16', $encoding);
    }

    /**
     * Tests that the best encoding comes from a wildcard in the Accept-Charset header
     */
    public function testBestEncodingComesFromWildcardInAcceptCharsetHeader() : void
    {
        $formatter = $this->createFormatterMock(['utf-8']);
        $acceptCharsetHeader = new AcceptCharsetHeaderValue('*');
        $encoding = $this->matcher->getBestEncodingMatch($formatter, [$acceptCharsetHeader], null);
        $this->assertEquals('utf-8', $encoding);
    }

    /**
     * Tests that the best encoding comes from a wildcard character in the Content-Type header
     */
    public function testBestEncodingComesFromWildcardInContentTypeHeader() : void
    {
        $formatter = $this->createFormatterMock(['utf-8']);
        $contentTypeHeader = new ContentTypeHeaderValue(
            'application/json',
            new ImmutableHashTable([new KeyValuePair('charset', '*')])
        );
        $encoding = $this->matcher->getBestEncodingMatch($formatter, [], $contentTypeHeader);
        $this->assertEquals('utf-8', $encoding);
    }

    /**
     * Tests that best encoding is chosen in order of quality score
     */
    public function testBestEncodingIsChosenInOrderOfQualityScore() : void
    {
        $formatter = $this->createFormatterMock(['utf-8', 'utf-16']);
        $acceptCharsetHeaders = [
            new AcceptCharsetHeaderValue('utf-8', new ImmutableHashTable([new KeyValuePair('q', 0.1)])),
            new AcceptCharsetHeaderValue('utf-16', new ImmutableHashTable([new KeyValuePair('q', 0.5)])),
        ];
        $encoding = $this->matcher->getBestEncodingMatch($formatter, $acceptCharsetHeaders, null);
        $this->assertEquals('utf-16', $encoding);
    }

    /**
     * Tests that the best encoding is null when the formatter does not support the charset from the Content-Type header
     */
    public function testBestEncodingIsNullWhenFormatterDoesNotSupportCharsetFromContentTypeHeader() : void
    {
        $formatter = $this->createFormatterMock(['utf-8']);
        $contentTypeHeaderParameters = new ImmutableHashTable([new KeyValuePair('charset', 'utf-16')]);
        $contentTypeHeader = new ContentTypeHeaderValue('text/html', $contentTypeHeaderParameters);
        $encoding = $this->matcher->getBestEncodingMatch($formatter, [], $contentTypeHeader);
        $this->assertNull($encoding);
    }

    /**
     * Tests that the best encoding is null when the formatter does not support the charset from the Accept-Charset header
     */
    public function testBestEncodingIsNullWhenFormatterDoesNotSupportCharsetFromAcceptCharsetHeader() : void
    {
        $formatter = $this->createFormatterMock(['utf-8']);
        $acceptCharsetHeader = new AcceptCharsetHeaderValue('utf-16');
        $encoding = $this->matcher->getBestEncodingMatch($formatter, [$acceptCharsetHeader], null);
        $this->assertNull($encoding);
    }

    /**
     * Tests that the best encoding is null when the formatter does not support the charset from the Accept header
     */
    public function testBestEncodingIsNullWhenFormatterDoesNotSupportCharsetFromAcceptHeader() : void
    {
        $formatter = $this->createFormatterMock(['utf-8']);
        $acceptHeaderParameters = new ImmutableHashTable([new KeyValuePair('charset', 'utf-16')]);
        $acceptHeader = new AcceptMediaTypeHeaderValue('text/html', $acceptHeaderParameters);
        $encoding = $this->matcher->getBestEncodingMatch($formatter, [], $acceptHeader);
        $this->assertNull($encoding);
    }

    /**
     * Tests that the best encoding is null when matching a zero-quality score charset
     */
    public function testBestEncodingIsNullWhenMatchingZeroQualityScoreCharset() : void
    {
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
     * @return IMediaTypeFormatter|\PHPUnit_Framework_MockObject_MockObject The mocked formatter
     */
    private function createFormatterMock(array $supportedEncodings) : IMediaTypeFormatter
    {
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $formatter->expects($this->once())
            ->method('getSupportedEncodings')
            ->willReturn($supportedEncodings);

        return $formatter;
    }
}
