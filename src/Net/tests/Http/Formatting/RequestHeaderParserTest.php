<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http\Formatting;

use Aphiria\Net\Http\Formatting\RequestHeaderParser;
use Aphiria\Net\Http\Headers;
use PHPUnit\Framework\TestCase;

class RequestHeaderParserTest extends TestCase
{
    private RequestHeaderParser $parser;
    private Headers $headers;

    protected function setUp(): void
    {
        $this->parser = new RequestHeaderParser();
        $this->headers = new Headers();
    }

    public function testParsingAcceptCharsetHeaderWithNoScoresReturnsValuesWithDefaultScores(): void
    {
        $headers = new Headers();
        $headers->add('Accept-Charset', 'utf-8', true);
        $headers->add('Accept-Charset', 'utf-16', true);
        $headerValues = $this->parser->parseAcceptCharsetHeader($headers);
        $this->assertCount(2, $headerValues);
        $this->assertSame('utf-8', $headerValues[0]->getCharset());
        $this->assertSame(1.0, $headerValues[0]->getQuality());
        $this->assertSame('utf-16', $headerValues[1]->getCharset());
        $this->assertSame(1.0, $headerValues[1]->getQuality());
    }

    public function testParsingAcceptCharsetHeaderWithScoresReturnsValuesWithThoseScores(): void
    {
        $headers = new Headers();
        $headers->add('Accept-Charset', 'utf-8; q=0.1', true);
        $headers->add('Accept-Charset', 'utf-16; q=0.5', true);
        $headerValues = $this->parser->parseAcceptCharsetHeader($headers);
        $this->assertCount(2, $headerValues);
        $this->assertSame('utf-8', $headerValues[0]->getCharset());
        $this->assertSame(0.1, $headerValues[0]->getQuality());
        $this->assertSame('utf-16', $headerValues[1]->getCharset());
        $this->assertSame(0.5, $headerValues[1]->getQuality());
    }

    public function testParsingAcceptHeaderWithCharsetSetsCharsetInHeaderValue(): void
    {
        $headers = new Headers();
        $headers->add('Accept', 'text/html; charset=utf-8', true);
        $headerValues = $this->parser->parseAcceptHeader($headers);
        $this->assertCount(1, $headerValues);
        $this->assertSame('utf-8', $headerValues[0]->getCharset());
    }

    public function testParsingAcceptHeaderWithNoScoresReturnsValuesWithDefaultScores(): void
    {
        $headers = new Headers();
        $headers->add('Accept', 'text/html', true);
        $headers->add('Accept', 'application/json', true);
        $headerValues = $this->parser->parseAcceptHeader($headers);
        $this->assertCount(2, $headerValues);
        $this->assertSame('text/html', $headerValues[0]->getMediaType());
        $this->assertSame(1.0, $headerValues[0]->getQuality());
        $this->assertNull($headerValues[0]->getCharset());
        $this->assertSame('application/json', $headerValues[1]->getMediaType());
        $this->assertSame(1.0, $headerValues[1]->getQuality());
        $this->assertNull($headerValues[1]->getCharset());
    }

    public function testParsingAcceptHeaderWithScoresReturnsValuesWithThoseScores(): void
    {
        $headers = new Headers();
        $headers->add('Accept', 'text/html; q=0.1', true);
        $headers->add('Accept', 'application/json; q=0.5', true);
        $headerValues = $this->parser->parseAcceptHeader($headers);
        $this->assertCount(2, $headerValues);
        $this->assertSame('text/html', $headerValues[0]->getMediaType());
        $this->assertSame(0.1, $headerValues[0]->getQuality());
        $this->assertNull($headerValues[0]->getCharset());
        $this->assertSame('application/json', $headerValues[1]->getMediaType());
        $this->assertSame(0.5, $headerValues[1]->getQuality());
        $this->assertNull($headerValues[1]->getCharset());
    }

    public function testParsingAcceptLanguageHeaderWithNoScoresReturnsValuesWithDefaultScores(): void
    {
        $headers = new Headers();
        $headers->add('Accept-Language', 'en-US', true);
        $headers->add('Accept-Language', 'en-GB', true);
        $headerValues = $this->parser->parseAcceptLanguageHeader($headers);
        $this->assertCount(2, $headerValues);
        $this->assertSame('en-US', $headerValues[0]->getLanguage());
        $this->assertSame(1.0, $headerValues[0]->getQuality());
        $this->assertSame('en-GB', $headerValues[1]->getLanguage());
        $this->assertSame(1.0, $headerValues[1]->getQuality());
    }

    public function testParsingAcceptLanguageHeaderWithScoresReturnsValuesWithThoseScores(): void
    {
        $headers = new Headers();
        $headers->add('Accept-Language', 'en-US; q=0.1', true);
        $headers->add('Accept-Language', 'en-GB; q=0.5', true);
        $headerValues = $this->parser->parseAcceptLanguageHeader($headers);
        $this->assertCount(2, $headerValues);
        $this->assertSame('en-US', $headerValues[0]->getLanguage());
        $this->assertSame(0.1, $headerValues[0]->getQuality());
        $this->assertSame('en-GB', $headerValues[1]->getLanguage());
        $this->assertSame(0.5, $headerValues[1]->getQuality());
    }

    public function testParsingContentTypeHeaderWithCharsetSetsCharset(): void
    {
        $headers = new Headers();
        $headers->add('Content-Type', 'application/json; charset=utf-8');
        $headerValue = $this->parser->parseContentTypeHeader($headers);
        $this->assertSame('application/json', $headerValue->getMediaType());
        $this->assertSame('utf-8', $headerValue->getCharset());
    }

    public function testParsingContentTypeHeaderWithNoCharsetStillSetsMediaType(): void
    {
        $headers = new Headers();
        $headers->add('Content-Type', 'application/json');
        $headerValue = $this->parser->parseContentTypeHeader($headers);
        $this->assertSame('application/json', $headerValue->getMediaType());
        $this->assertNull($headerValue->getCharset());
    }

    public function testParsingCookiesReturnsCorrectValuesWithMultipleCookieValues(): void
    {
        $this->headers->add('Cookie', 'foo=bar; baz=blah');
        $cookies = $this->parser->parseCookies($this->headers);
        $this->assertSame('bar', $cookies->get('foo'));
        $this->assertSame('blah', $cookies->get('baz'));
    }

    public function testParsingCookiesAndNotHavingCookieHeaderReturnsEmptyDictionary(): void
    {
        $this->assertSame(0, $this->parser->parseCookies($this->headers)->count());
    }

    public function testParsingNonExistentAcceptCharsetHeaderReturnsEmptyArray(): void
    {
        $headers = new Headers();
        $this->assertEquals([], $this->parser->parseAcceptCharsetHeader($headers));
    }

    public function testParsingNonExistentAcceptHeaderReturnsEmptyArray(): void
    {
        $headers = new Headers();
        $this->assertEquals([], $this->parser->parseAcceptHeader($headers));
    }

    public function testParsingNonExistentContentHeaderReturnsNull(): void
    {
        $this->assertNull($this->parser->parseContentTypeHeader(new Headers()));
    }
}
