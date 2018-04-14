<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting;

use Opulence\Net\Http\Formatting\RequestHeaderParser;
use Opulence\Net\Http\HttpHeaders;

/**
 * Tests the request header parser
 */
class RequestHeaderParserTest extends \PHPUnit\Framework\TestCase
{
    /** @var RequestHeaderParser The parser to use in tests */
    private $parser;
    /** @var HttpHeaders The headers to use in tests */
    private $headers;

    public function setUp(): void
    {
        $this->parser = new RequestHeaderParser();
        $this->headers = new HttpHeaders();
    }

    public function testParsingAcceptCharsetHeaderWithNoScoresReturnsValuesWithDefaultScores(): void
    {
        $headers = new HttpHeaders();
        $headers->add('Accept-Charset', 'utf-8', true);
        $headers->add('Accept-Charset', 'utf-16', true);
        $headerValues = $this->parser->parseAcceptCharsetHeader($headers);
        $this->assertCount(2, $headerValues);
        $this->assertEquals('utf-8', $headerValues[0]->getCharset());
        $this->assertEquals(1.0, $headerValues[0]->getQuality());
        $this->assertEquals('utf-16', $headerValues[1]->getCharset());
        $this->assertEquals(1.0, $headerValues[1]->getQuality());
    }

    public function testParsingAcceptCharsetHeaderWithScoresReturnsValuesWithThoseScores(): void
    {
        $headers = new HttpHeaders();
        $headers->add('Accept-Charset', 'utf-8; q=0.1', true);
        $headers->add('Accept-Charset', 'utf-16; q=0.5', true);
        $headerValues = $this->parser->parseAcceptCharsetHeader($headers);
        $this->assertCount(2, $headerValues);
        $this->assertEquals('utf-8', $headerValues[0]->getCharset());
        $this->assertEquals(0.1, $headerValues[0]->getQuality());
        $this->assertEquals('utf-16', $headerValues[1]->getCharset());
        $this->assertEquals(0.5, $headerValues[1]->getQuality());
    }

    public function testParsingAcceptHeaderWithCharsetSetsCharsetInHeaderValue(): void
    {
        $headers = new HttpHeaders();
        $headers->add('Accept', 'text/html; charset=utf-8', true);
        $headerValues = $this->parser->parseAcceptHeader($headers);
        $this->assertCount(1, $headerValues);
        $this->assertEquals('utf-8', $headerValues[0]->getCharset());
    }

    public function testParsingAcceptHeaderWithNoScoresReturnsValuesWithDefaultScores(): void
    {
        $headers = new HttpHeaders();
        $headers->add('Accept', 'text/html', true);
        $headers->add('Accept', 'application/json', true);
        $headerValues = $this->parser->parseAcceptHeader($headers);
        $this->assertCount(2, $headerValues);
        $this->assertEquals('text/html', $headerValues[0]->getMediaType());
        $this->assertEquals(1.0, $headerValues[0]->getQuality());
        $this->assertNull($headerValues[0]->getCharset());
        $this->assertEquals('application/json', $headerValues[1]->getMediaType());
        $this->assertEquals(1.0, $headerValues[1]->getQuality());
        $this->assertNull($headerValues[1]->getCharset());
    }

    public function testParsingAcceptHeaderWithScoresReturnsValuesWithThoseScores(): void
    {
        $headers = new HttpHeaders();
        $headers->add('Accept', 'text/html; q=0.1', true);
        $headers->add('Accept', 'application/json; q=0.5', true);
        $headerValues = $this->parser->parseAcceptHeader($headers);
        $this->assertCount(2, $headerValues);
        $this->assertEquals('text/html', $headerValues[0]->getMediaType());
        $this->assertEquals(0.1, $headerValues[0]->getQuality());
        $this->assertNull($headerValues[0]->getCharset());
        $this->assertEquals('application/json', $headerValues[1]->getMediaType());
        $this->assertEquals(0.5, $headerValues[1]->getQuality());
        $this->assertNull($headerValues[1]->getCharset());
    }

    public function testParsingAcceptLanguageHeaderWithNoScoresReturnsValuesWithDefaultScores(): void
    {
        $headers = new HttpHeaders();
        $headers->add('Accept-Language', 'en-US', true);
        $headers->add('Accept-Language', 'en-GB', true);
        $headerValues = $this->parser->parseAcceptLanguageHeader($headers);
        $this->assertCount(2, $headerValues);
        $this->assertEquals('en-US', $headerValues[0]->getLanguage());
        $this->assertEquals(1.0, $headerValues[0]->getQuality());
        $this->assertEquals('en-GB', $headerValues[1]->getLanguage());
        $this->assertEquals(1.0, $headerValues[1]->getQuality());
    }

    public function testParsingAcceptLanguageHeaderWithScoresReturnsValuesWithThoseScores(): void
    {
        $headers = new HttpHeaders();
        $headers->add('Accept-Language', 'en-US; q=0.1', true);
        $headers->add('Accept-Language', 'en-GB; q=0.5', true);
        $headerValues = $this->parser->parseAcceptLanguageHeader($headers);
        $this->assertCount(2, $headerValues);
        $this->assertEquals('en-US', $headerValues[0]->getLanguage());
        $this->assertEquals(0.1, $headerValues[0]->getQuality());
        $this->assertEquals('en-GB', $headerValues[1]->getLanguage());
        $this->assertEquals(0.5, $headerValues[1]->getQuality());
    }

    public function testParsingContentTypeHeaderWithCharsetSetsCharset(): void
    {
        $headers = new HttpHeaders();
        $headers->add('Content-Type', 'application/json; charset=utf-8');
        $headerValue = $this->parser->parseContentTypeHeader($headers);
        $this->assertEquals('application/json', $headerValue->getMediaType());
        $this->assertEquals('utf-8', $headerValue->getCharset());
    }

    public function testParsingContentTypeHeaderWithNoCharsetStillSetsMediaType(): void
    {
        $headers = new HttpHeaders();
        $headers->add('Content-Type', 'application/json');
        $headerValue = $this->parser->parseContentTypeHeader($headers);
        $this->assertEquals('application/json', $headerValue->getMediaType());
        $this->assertNull($headerValue->getCharset());
    }

    public function testParsingCookiesReturnsCorrectValuesWithMultipleCookieValues(): void
    {
        $this->headers->add('Cookie', 'foo=bar; baz=blah');
        $cookies = $this->parser->parseCookies($this->headers);
        $this->assertEquals('bar', $cookies->get('foo'));
        $this->assertEquals('blah', $cookies->get('baz'));
    }

    public function testParsingCookiesAndNotHavingCookieHeaderReturnsEmptyDictionary(): void
    {
        $this->assertEquals(0, $this->parser->parseCookies($this->headers)->count());
    }

    public function testParsingNonExistentAcceptCharsetHeaderReturnsEmptyArray(): void
    {
        $headers = new HttpHeaders();
        $this->assertEquals([], $this->parser->parseAcceptCharsetHeader($headers));
    }

    public function testParsingNonExistentAcceptHeaderReturnsEmptyArray(): void
    {
        $headers = new HttpHeaders();
        $this->assertEquals([], $this->parser->parseAcceptHeader($headers));
    }

    public function testParsingNonExistentContentHeaderReturnsNull(): void
    {
        $this->assertNull($this->parser->parseContentTypeHeader(new HttpHeaders()));
    }
}
