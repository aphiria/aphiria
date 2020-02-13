<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http\Formatting;

use Aphiria\Net\Http\ContentNegotiation\AcceptCharsetEncodingMatcher;
use Aphiria\Net\Http\Formatting\RequestHeaderParser;
use Aphiria\Net\Http\HttpHeaders;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\Request;
use Aphiria\Net\Uri;
use PHPUnit\Framework\TestCase;

/**
 * Tests the Accept-Charset encoding matcher
 */
class AcceptCharsetEncodingMatcherTest extends TestCase
{
    private AcceptCharsetEncodingMatcher $matcher;
    private RequestHeaderParser $headerParser;
    private HttpHeaders $headers;
    private IHttpRequestMessage $request;

    protected function setUp(): void
    {
        $this->matcher = new AcceptCharsetEncodingMatcher();
        $this->headerParser = new RequestHeaderParser();
        $this->headers = new HttpHeaders();
        $this->request = new Request('GET', new Uri('http://example.com'), $this->headers);
    }

    public function testBestEncodingCanMatchMismatchingCasesInAcceptCharset(): void
    {
        $this->headers->add('Accept-Charset', 'UTF-8');
        $this->assertEquals('utf-8', $this->matcher->getBestEncodingMatch(['utf-8'], $this->request));
    }

    public function testBestEncodingCanMatchMismatchingCasesInAcceptHeader(): void
    {
        $this->headers->add('Accept', 'text/html; charset=UTF-8');
        $encoding = $this->matcher->getBestEncodingMatch(
            ['utf-8'],
            $this->request,
            $this->headerParser->parseAcceptHeader($this->headers)[0]
        );
        $this->assertEquals('utf-8', $encoding);
    }

    public function testBestEncodingComesFromAcceptCharsetHeaderIfItAndAcceptHeaderHaveSupportedEncodings(): void
    {
        $this->headers->add('Accept-Charset', 'utf-16');
        $this->headers->add('Accept', 'text/html; charset=UTF-8');
        $encoding = $this->matcher->getBestEncodingMatch(
            ['utf-8', 'utf-16'],
            $this->request,
            $this->headerParser->parseAcceptHeader($this->headers)[0]
        );
        $this->assertEquals('utf-16', $encoding);
    }

    public function testBestEncodingComesFromAcceptCharsetHeaderIfNoAcceptHeaderIsPresent(): void
    {
        $this->headers->add('Accept-Charset', 'utf-16');
        $encoding = $this->matcher->getBestEncodingMatch(['utf-16'], $this->request);
        $this->assertEquals('utf-16', $encoding);
    }

    public function testBestEncodingComesFromAcceptHeaderIfAcceptCharsetHeaderIsNotPresent(): void
    {
        $this->headers->add('Accept', 'text/html; charset=utf-16');
        $encoding = $this->matcher->getBestEncodingMatch(
            ['utf-16'],
            $this->request,
            $this->headerParser->parseAcceptHeader($this->headers)[0]
        );
        $this->assertEquals('utf-16', $encoding);
    }

    public function testBestEncodingComesFromWildcardInAcceptCharsetHeader(): void
    {
        $this->headers->add('Accept-Charset', '*');
        $encoding = $this->matcher->getBestEncodingMatch(['utf-8'], $this->request);
        $this->assertEquals('utf-8', $encoding);
    }

    public function testBestEncodingComesFromWildcardInContentTypeHeader(): void
    {
        $this->headers->add('Content-Type', 'application/json; charset=*');
        $encoding = $this->matcher->getBestEncodingMatch(
            ['utf-8'],
            $this->request,
            $this->headerParser->parseContentTypeHeader($this->headers)
        );
        $this->assertEquals('utf-8', $encoding);
    }

    public function testBestEncodingIsChosenInOrderOfQualityScore(): void
    {
        $this->headers->add('Accept-Charset', 'utf-8; q=0.1');
        $this->headers->add('Accept-Charset', 'utf-16; q=0.5', true);
        $encoding = $this->matcher->getBestEncodingMatch(['utf-8', 'utf-16'], $this->request);
        $this->assertEquals('utf-16', $encoding);
    }

    public function testBestEncodingIsNullWhenFormatterDoesNotSupportCharsetFromContentTypeHeader(): void
    {
        $this->headers->add('Content-Type', 'text/html; charset=utf-16');
        $encoding = $this->matcher->getBestEncodingMatch(
            ['utf-8'],
            $this->request, $this->headerParser->parseContentTypeHeader($this->headers)
        );
        $this->assertNull($encoding);
    }

    public function testBestEncodingIsNullWhenFormatterDoesNotSupportCharsetFromAcceptCharsetHeader(): void
    {
        $this->headers->add('Accept-Charset', 'utf-16');
        $encoding = $this->matcher->getBestEncodingMatch(['utf-8'], $this->request);
        $this->assertNull($encoding);
    }

    public function testBestEncodingIsNullWhenFormatterDoesNotSupportCharsetFromAcceptHeader(): void
    {
        $this->headers->add('Accept', 'text/html; charset=utf-16');
        $encoding = $this->matcher->getBestEncodingMatch(
            ['utf-8'],
            $this->request,
            $this->headerParser->parseAcceptHeader($this->headers)[0]
        );
        $this->assertNull($encoding);
    }

    public function testBestEncodingIsNullWhenMatchingZeroQualityScoreCharset(): void
    {
        $this->headers->add('Accept-Charset', 'utf-8; q=0.0');
        $encoding = $this->matcher->getBestEncodingMatch(['utf-8'], $this->request);
        $this->assertNull($encoding);
    }
}
