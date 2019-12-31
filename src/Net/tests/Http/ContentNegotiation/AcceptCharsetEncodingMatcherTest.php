<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http\Formatting;

use Aphiria\Net\Http\ContentNegotiation\AcceptCharsetEncodingMatcher;
use Aphiria\Net\Http\Formatting\RequestHeaderParser;
use Aphiria\Net\Http\HttpHeaders;
use PHPUnit\Framework\TestCase;

/**
 * Tests the Accept-Charset encoding matcher
 */
class AcceptCharsetEncodingMatcherTest extends TestCase
{
    private AcceptCharsetEncodingMatcher $matcher;
    private RequestHeaderParser $headerParser;

    protected function setUp(): void
    {
        $this->matcher = new AcceptCharsetEncodingMatcher();
        $this->headerParser = new RequestHeaderParser();
    }

    public function testBestEncodingCanMatchMismatchingCasesInAcceptCharset(): void
    {
        $headers = new HttpHeaders();
        $headers->add('Accept-Charset', 'UTF-8');
        $this->assertEquals('utf-8', $this->matcher->getBestEncodingMatch(['utf-8'], $headers));
    }

    public function testBestEncodingCanMatchMismatchingCasesInAcceptHeader(): void
    {
        $headers = new HttpHeaders();
        $headers->add('Accept', 'text/html; charset=UTF-8');
        $encoding = $this->matcher->getBestEncodingMatch(['utf-8'], $headers, $this->headerParser->parseAcceptHeader($headers)[0]);
        $this->assertEquals('utf-8', $encoding);
    }

    public function testBestEncodingComesFromAcceptCharsetHeaderIfItAndAcceptHeaderHaveSupportedEncodings(): void
    {
        $headers = new HttpHeaders();
        $headers->add('Accept-Charset', 'utf-16');
        $headers->add('Accept', 'text/html; charset=UTF-8');
        $encoding = $this->matcher->getBestEncodingMatch(['utf-8', 'utf-16'], $headers, $this->headerParser->parseAcceptHeader($headers)[0]);
        $this->assertEquals('utf-16', $encoding);
    }

    public function testBestEncodingComesFromAcceptCharsetHeaderIfNoAcceptHeaderIsPresent(): void
    {
        $headers = new HttpHeaders();
        $headers->add('Accept-Charset', 'utf-16');
        $encoding = $this->matcher->getBestEncodingMatch(['utf-16'], $headers);
        $this->assertEquals('utf-16', $encoding);
    }

    public function testBestEncodingComesFromAcceptHeaderIfAcceptCharsetHeaderIsNotPresent(): void
    {
        $headers = new HttpHeaders();
        $headers->add('Accept', 'text/html; charset=utf-16');
        $encoding = $this->matcher->getBestEncodingMatch(['utf-16'], $headers, $this->headerParser->parseAcceptHeader($headers)[0]);
        $this->assertEquals('utf-16', $encoding);
    }

    public function testBestEncodingComesFromWildcardInAcceptCharsetHeader(): void
    {
        $headers = new HttpHeaders();
        $headers->add('Accept-Charset', '*');
        $encoding = $this->matcher->getBestEncodingMatch(['utf-8'], $headers);
        $this->assertEquals('utf-8', $encoding);
    }

    public function testBestEncodingComesFromWildcardInContentTypeHeader(): void
    {
        $headers = new HttpHeaders();
        $headers->add('Content-Type', 'application/json; charset=*');
        $encoding = $this->matcher->getBestEncodingMatch(['utf-8'], $headers, $this->headerParser->parseContentTypeHeader($headers));
        $this->assertEquals('utf-8', $encoding);
    }

    public function testBestEncodingIsChosenInOrderOfQualityScore(): void
    {
        $headers = new HttpHeaders();
        $headers->add('Accept-Charset', 'utf-8; q=0.1');
        $headers->add('Accept-Charset', 'utf-16; q=0.5', true);
        $encoding = $this->matcher->getBestEncodingMatch(['utf-8', 'utf-16'], $headers);
        $this->assertEquals('utf-16', $encoding);
    }

    public function testBestEncodingIsNullWhenFormatterDoesNotSupportCharsetFromContentTypeHeader(): void
    {
        $headers = new HttpHeaders();
        $headers->add('Content-Type', 'text/html; charset=utf-16');
        $encoding = $this->matcher->getBestEncodingMatch(['utf-8'], $headers, $this->headerParser->parseContentTypeHeader($headers));
        $this->assertNull($encoding);
    }

    public function testBestEncodingIsNullWhenFormatterDoesNotSupportCharsetFromAcceptCharsetHeader(): void
    {
        $headers = new HttpHeaders();
        $headers->add('Accept-Charset', 'utf-16');
        $encoding = $this->matcher->getBestEncodingMatch(['utf-8'], $headers);
        $this->assertNull($encoding);
    }

    public function testBestEncodingIsNullWhenFormatterDoesNotSupportCharsetFromAcceptHeader(): void
    {
        $headers = new HttpHeaders();
        $headers->add('Accept', 'text/html; charset=utf-16');
        $encoding = $this->matcher->getBestEncodingMatch(['utf-8'], $headers, $this->headerParser->parseAcceptHeader($headers)[0]);
        $this->assertNull($encoding);
    }

    public function testBestEncodingIsNullWhenMatchingZeroQualityScoreCharset(): void
    {
        $headers = new HttpHeaders();
        $headers->add('Accept-Charset', 'utf-8; q=0.0');
        $encoding = $this->matcher->getBestEncodingMatch(['utf-8'], $headers);
        $this->assertNull($encoding);
    }
}
