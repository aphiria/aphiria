<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests;

use Opulence\Net\Uri;
use Opulence\Net\UriParser;

/**
 * Tests the URI parser
 */
class UriParserTest extends \PHPUnit\Framework\TestCase
{
    /** @var UriParser The URI parser to use in tests */
    private $parser = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->parser = new UriParser();
    }

    /**
     * Tests that parsing the query string param with multiple values returns an array of values
     */
    public function testParsingQueryStringParamWithMultipleValuesReturnsArrayOfValues() : void
    {
        $uri = new Uri('http://host.com?foo[]=bar&foo[]=baz');
        $values = $this->parser->parseQueryString($uri);
        $this->assertEquals(['bar', 'baz'], $values->get('foo'));
    }

    /**
     * Tests that parsing the query string param without a value returns false
     */
    public function testParsingQueryStringParamWithoutValueReturnsFalse() : void
    {
        $uri = new Uri('http://host.com?foo=bar');
        $this->assertFalse($this->parser->parseQueryString($uri)->containsKey('baz'));
    }

    /**
     * Tests that parsing the query string param with a single value returns that value
     */
    public function testParsingQueryStringParamWithSingleValueReturnsThatValue() : void
    {
        $uri = new Uri('http://host.com?foo=bar');
        $this->assertEquals('bar', $this->parser->parseQueryString($uri)->get('foo'));
    }

    /**
     * Tests that URL-encoded values are decoded
     */
    public function testUrlEncodedValuesAreDecoded() : void
    {
        $uri = new Uri('http://host.com?foo=a%26w');
        $this->assertEquals('a&w', $this->parser->parseQueryString($uri)->get('foo'));
    }
}
