<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net;

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
        $uri = new Uri('http', null, null, 'host.com', null, '', 'foo[]=bar&foo[]=baz', null);
        $this->assertEquals(['foo' => ['bar', 'baz']], $this->parser->parseQueryString($uri)->toArray());
    }

    /**
     * Tests that parsing the query string param without a value returns null
     */
    public function testParsingQueryStringParamWithoutValueReturnsNull() : void
    {
        $uri = new Uri('http', null, null, 'host.com', null, '', 'foo=bar', null);
        $this->assertNull($this->parser->parseQueryString($uri)->get('baz'));
    }

    /**
     * Tests that parsing the query string param with a single value returns that value
     */
    public function testParsingQueryStringParamWithSingleValueReturnsThatValue() : void
    {
        $uri = new Uri('http', null, null, 'host.com', null, '', 'foo=bar', null);
        $this->assertEquals(['foo' => 'bar'], $this->parser->parseQueryString($uri)->toArray());
        $this->assertEquals('bar', $this->parser->parseQueryString($uri)->get('foo'));
    }

    /**
     * Tests that URL-encoded values are decoded
     */
    public function testUrlEncodedValuesAreDecoded() : void
    {
        $uri = new Uri('http', null, null, 'host.com', null, '', 'foo=a%26w', null);
        $this->assertEquals(['foo' => 'a&w'], $this->parser->parseQueryString($uri)->toArray());
        $this->assertEquals('a&w', $this->parser->parseQueryString($uri)->get('foo'));
    }
}
