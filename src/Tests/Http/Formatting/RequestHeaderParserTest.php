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
    private $parser = null;
    /** @var HttpHeaders The headers to use in tests */
    private $headers = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->parser = new RequestHeaderParser();
        $this->headers = new HttpHeaders();
    }

    /**
     * Tests that the charset header value overrides the charset in the Accept header
     */
    public function testAcceptCharsetHeaderValueIsUsedIfNoneSpecifiedInAcceptHeader() : void
    {
        $headers = new HttpHeaders();
        $headers->add('Accept', 'text/html; charset=utf-8', true);
        $headers->add('Accept', 'text/plain', true);
        $headers->add('Accept-Charset', 'utf-16');
        $headerValues = $this->parser->parseAcceptParameters($headers);
        $this->assertCount(2, $headerValues);
        $this->assertEquals('utf-8', $headerValues[0]->getCharSet());
        // Since this Accept header value had no charset, it should default to the Accept-Charset header value
        $this->assertEquals('utf-16', $headerValues[1]->getCharSet());
    }

    /**
     * Tests that parsing an Accept header with a charset sets the charset in the header value
     */
    public function testParsingAcceptHeaderWithCharSetSetsCharsetInHeaderValue() : void
    {
        $headers = new HttpHeaders();
        $headers->add('Accept', 'text/html; charset=utf-8', true);
        $headerValues = $this->parser->parseAcceptParameters($headers);
        $this->assertCount(1, $headerValues);
        $this->assertEquals('utf-8', $headerValues[0]->getCharSet());
    }

    /**
     * Tests that parsing an Accept header with no scores returns values with default scores
     */
    public function testParsingAcceptHeaderWithNoScoresReturnsValuesWithDefaultScores() : void
    {
        $headers = new HttpHeaders();
        $headers->add('Accept', 'text/html', true);
        $headers->add('Accept', 'application/json', true);
        $headerValues = $this->parser->parseAcceptParameters($headers);
        $this->assertCount(2, $headerValues);
        $this->assertEquals('text/html', $headerValues[0]->getFullMediaType());
        $this->assertEquals(1.0, $headerValues[0]->getQuality());
        $this->assertNull($headerValues[0]->getCharSet());
        $this->assertEquals('application/json', $headerValues[1]->getFullMediaType());
        $this->assertEquals(1.0, $headerValues[1]->getQuality());
        $this->assertNull($headerValues[1]->getCharSet());
    }

    /**
     * Tests that parsing an Accept header with scores returns values with those scores
     */
    public function testParsingAcceptHeaderWithScoresReturnsValuesWithThoseScores() : void
    {
        $headers = new HttpHeaders();
        $headers->add('Accept', 'text/html; q=0.1', true);
        $headers->add('Accept', 'application/json; q=0.5', true);
        $headerValues = $this->parser->parseAcceptParameters($headers);
        $this->assertCount(2, $headerValues);
        $this->assertEquals('text/html', $headerValues[0]->getFullMediaType());
        $this->assertEquals(0.1, $headerValues[0]->getQuality());
        $this->assertNull($headerValues[0]->getCharSet());
        $this->assertEquals('application/json', $headerValues[1]->getFullMediaType());
        $this->assertEquals(0.5, $headerValues[1]->getQuality());
        $this->assertNull($headerValues[1]->getCharSet());
    }

    /**
     * Tests that parsing cookies returns the correct values with multiple cookie values
     */
    public function testParsingCookiesReturnsCorrectValuesWithMultipleCookieValues() : void
    {
        $this->headers->add('Cookie', 'foo=bar; baz=blah');
        $cookies = $this->parser->parseCookies($this->headers);
        $this->assertEquals('bar', $cookies->get('foo'));
        $this->assertEquals('blah', $cookies->get('baz'));
    }

    /**
     * Tests that parsing cookies and not having a cookie header returns an empty dictionary
     */
    public function testParsingCookiesAndNotHavingCookieHeaderReturnsEmptyDictionary() : void
    {
        $this->assertEquals(0, $this->parser->parseCookies($this->headers)->count());
    }

    /**
     * Tests that parsing an non-existent Accept header returns an empty array
     */
    public function testParsingNonExistentAcceptHeaderReturnsEmptyArray() : void
    {
        $headers = new HttpHeaders();
        $this->assertEquals([], $this->parser->parseAcceptParameters($headers));
    }
}
