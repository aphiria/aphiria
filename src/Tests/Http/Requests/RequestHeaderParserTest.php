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
}
