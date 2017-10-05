<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Requests;

use InvalidArgumentException;
use Opulence\Net\Http\Collection;
use Opulence\Net\Http\HttpHeaders;

/**
 * Tests the HTTP request header parser
 */
class HttpRequestHeaderParserTest extends \PHPUnit\Framework\TestCase
{
    /** @var HttpRequestHeaderParser The parser to use in tests */
    private $parser = null;
    /** @var HttpHeaders The headers to use in tests */
    private $headers = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->parser = new HttpRequestHeaderParser();
        $this->headers = new HttpHeaders();
    }

    /**
     * Tests that a bad cookie format throws an exception when getting all cookies
     */
    public function testBadCookieFormatThrowsExceptionWhenGettingAllCookies() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->headers->add('Cookie', 'foo=');
        $this->parser->parseCookies($this->headers);
    }

    /**
     * Tests checking if the headers indicate a JSON response with the value of the content type header
     */
    public function testCheckingIfJsonChecksContentTypeHeader()
    {
        $this->headers->add('Content-Type', 'text/plain');
        $this->assertFalse($this->parser->isJson($this->headers));
        $this->headers->remove('Content-Type');
        $this->headers->add('Content-Type', 'application/json');
        $this->assertTrue($this->parser->isJson($this->headers));
        $this->headers->remove('Content-Type');
        $this->headers->add('Content-Type', 'application/json; charset=utf-8');
        $this->assertTrue($this->parser->isJson($this->headers));
    }

    /**
     * Tests checking if the headers indicate an XHR request with the value of the X-Requested-With header
     */
    public function testCheckingIfXhrChecksXRequestedWithHeader()
    {
        $this->headers->add('X-Requested-With', 'XMLHttpRequest');
        $this->assertTrue($this->parser->isXhr($this->headers));
        $this->headers->remove('X-Requested-With');
        $this->assertFalse($this->parser->isXhr($this->headers));
    }

    /**
     * Tests that getting all cookies with multiple cookies set returns the correct mapping
     */
    public function testGettingAllCookiesWithMultipleCookiesReturnsCorrectMapping() : void
    {
        $this->headers->add('Cookie', 'foo=bar; baz=blah');
        $this->assertEquals(['foo' => 'bar', 'baz' => 'blah'], $this->parser->parseCookies($this->headers)->getAll());
    }

    /**
     * Tests that getting all cookies with one cookie set returns the correct mapping
     */
    public function testGettingAllCookiesWithOneCookieReturnsCorrectMapping() : void
    {
        $this->headers->add('Cookie', 'foo=bar');
        $this->assertEquals(['foo' => 'bar'], $this->parser->parseCookies($this->headers)->getAll());
    }
    
    /**
     * Tests getting cookies returns the same instance of the collection
     */
    public function testGettingCookiesReturnsSameInstanceOfCollection() : void
    {
        $this->headers->add('Cookie', 'foo=bar');
        $expectedCollection = $this->parser->parseCookies($this->headers);
        $this->assertSame($expectedCollection, $this->parser->parseCookies($this->headers));
    }

    /**
     * Tests getting a cookie value URL decodes the value
     */
    public function testGettingCookieValueUrlDecodesValue() : void
    {
        $this->headers->add('Cookie', 'foo=' . urlencode('%') . '; bar=' . urlencode(';'));
        $this->assertEquals('%', $this->parser->parseCookies($this->headers)->get('foo'));
        $this->assertEquals(';', $this->parser->parseCookies($this->headers)->get('bar'));
        $this->assertEquals(['foo' => '%', 'bar' => ';'], $this->parser->parseCookies($this->headers)->getAll());
    }

    /**
     * Tests that a set 'Cookie' header without a cookie name returns a null cookie value
     */
    public function testSetCookieHeaderWithoutCookieNameReturnsNullCookieValue() : void
    {
        $this->headers->add('Cookie', 'foo=bar');
        $this->assertNull($this->parser->parseCookies($this->headers)->get('baz'));
    }

    /**
     * Tests that an unset 'Cookie' header returns an empty cookie array
     */
    public function testUnsetCookieHeaderReturnsEmptyCookies() : void
    {
        $this->assertEquals([], $this->parser->parseCookies($this->headers)->getAll());
    }

    /**
     * Tests that an unset 'Cookie' header returns a null cookie value
     */
    public function testUnsetCookieHeaderReturnsNullCookieValue() : void
    {
        $this->assertNull($this->parser->parseCookies($this->headers)->get('foo'));
    }
}
