<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ContentNegotiation\Tests;

use Aphiria\ContentNegotiation\AcceptCharsetEncodingMatcher;
use Aphiria\Net\Http\Formatting\RequestHeaderParser;
use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\Request;
use Aphiria\Net\Uri;
use PHPUnit\Framework\TestCase;

class AcceptCharsetEncodingMatcherTest extends TestCase
{
    private AcceptCharsetEncodingMatcher $matcher;
    private RequestHeaderParser $headerParser;
    private Headers $headers;
    private IRequest $request;

    protected function setUp(): void
    {
        $this->matcher = new AcceptCharsetEncodingMatcher();
        $this->headerParser = new RequestHeaderParser();
        $this->headers = new Headers();
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
        $this->headers->add('Accept-Charset', 'utf-32; q=0.6', true);
        $encoding = $this->matcher->getBestEncodingMatch(['utf-8', 'utf-16', 'utf-32'], $this->request);
        $this->assertEquals('utf-32', $encoding);
    }

    public function testBestEncodingIsFirstSupportedOneIfBothHaveEqualScoresAndAreNotWildcards(): void
    {
        $this->headers->add('Accept-Charset', 'utf-8');
        $this->headers->add('Accept-Charset', 'utf-16', true);
        $encoding = $this->matcher->getBestEncodingMatch(['utf-8', 'utf-16'], $this->request);
        $this->assertEquals('utf-8', $encoding);
    }

    public function testBestEncodingIsFirstSupportedOneIfBothHaveEqualScoresAndAreWildcards(): void
    {
        $this->headers->add('Accept-Charset', '*');
        $this->headers->add('Accept-Charset', '*', true);
        $encoding = $this->matcher->getBestEncodingMatch(['utf-8'], $this->request);
        $this->assertEquals('utf-8', $encoding);
    }

    public function testBestEncodingIsNonWildcardIfBothHaveEqualsScoresAndWildcardIsBeforeNon(): void
    {
        $this->headers->add('Accept-Charset', '*');
        $this->headers->add('Accept-Charset', 'utf-8', true);
        $encoding = $this->matcher->getBestEncodingMatch(['utf-8'], $this->request);
        $this->assertEquals('utf-8', $encoding);
    }

    public function testBestEncodingIsNonWildcardIfBothHaveEqualsScoresAndWildcardisAfterNon(): void
    {
        $this->headers->add('Accept-Charset', 'utf-8');
        $this->headers->add('Accept-Charset', '*', true);
        $encoding = $this->matcher->getBestEncodingMatch(['utf-8'], $this->request);
        $this->assertEquals('utf-8', $encoding);
    }

    public function testBestEncodingIsNullWhenFormatterDoesNotSupportCharsetFromContentTypeHeader(): void
    {
        $this->headers->add('Content-Type', 'text/html; charset=utf-16');
        $encoding = $this->matcher->getBestEncodingMatch(
            ['utf-8'],
            $this->request,
            $this->headerParser->parseContentTypeHeader($this->headers)
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
