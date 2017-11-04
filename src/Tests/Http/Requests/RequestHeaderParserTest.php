<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Requests;

use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\Requests\RequestHeaderParser;
use OutOfBoundsException;

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
     * Tests that getting all cookies returns the correct values with multiple cookie values
     */
    public function testGettingAllCookiesReturnsCorrectValuesWithMultipleCookieValues() : void
    {
        $this->headers->add('Cookie', 'foo=bar; baz=blah');
        $cookies = $this->parser->getAllCookies($this->headers);
        $this->assertEquals('bar', $cookies->get('foo'));
        $this->assertEquals('blah', $cookies->get('baz'));
    }

    /**
     * Tests that getting a cookie returns the correct value with multiple cookie values
     */
    public function testGettingCookieReturnsCorrectValueWithMultipleCookieValues() : void
    {
        $this->headers->add('Cookie', 'foo=bar; baz=blah');
        $this->assertEquals('bar', $this->parser->getCookie($this->headers, 'foo'));
        $this->assertEquals('blah', $this->parser->getCookie($this->headers, 'baz'));
    }

    /**
     * Tests that getting a cookie returns the correct value with a single cookie value
     */
    public function testGettingCookieReturnsCorrectValueWithSingleCookieValue() : void
    {
        $this->headers->add('Cookie', 'foo=bar');
        $this->assertEquals('bar', $this->parser->getCookie($this->headers, 'foo'));
    }

    /**
     * Tests that getting all cookies and not having a cookie header throws an exception
     */
    public function testGettingAllCookiesAndNotHavingCookieHeaderThrowsException() : void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->parser->getAllCookies($this->headers);
    }

    /**
     * Tests that getting a cookie and not having a cookie header throws an exception
     */
    public function testGettingCookieAndNotHavingCookieHeaderThrowsException() : void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->parser->getCookie($this->headers, 'foo');
    }

    /**
     * Tests that getting a cookie and not having a particular cookie throws an exception
     */
    public function testGettingCookieAndNotHavingParticularCookieHeaderThrowsException() : void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->headers->add('Cookie', 'foo=bar');
        $this->parser->getCookie($this->headers, 'baz');
    }
}
