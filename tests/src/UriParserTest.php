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
     * Tests that checking the existence of a query string param returns correct value
     */
    public function testCheckingExistenceOfQueryStringParamReturnsCorrectValue() : void
    {
        $uri = new Uri('http', null, null, 'host.com', null, '', 'foo=bar', null);
        $this->assertTrue($this->parser->hasQueryStringParam($uri, 'foo'));
        $this->assertFalse($this->parser->hasQueryStringParam($uri, 'baz'));
    }

    /**
     * Tests that getting the query string param with multiple values returns an array of values
     */
    public function testGettingQueryStringParamWithMultipleValuesReturnsArrayOfValues() : void
    {
        $uri = new Uri('http', null, null, 'host.com', null, '', 'foo[]=bar&foo[]=baz', null);
        $this->assertEquals(['bar', 'baz'], $this->parser->getQueryStringParam($uri, 'foo'));
    }

    /**
     * Tests that getting the query string param without a value returns null
     */
    public function testGettingQueryStringParamWithoutValueReturnsNull() : void
    {
        $uri = new Uri('http', null, null, 'host.com', null, '', 'foo=bar', null);
        $this->assertNull($this->parser->getQueryStringParam($uri, 'baz'));
    }

    /**
     * Tests that getting the query string param with a single value returns that value
     */
    public function testGettingQueryStringParamWithSingleValueReturnsThatValue() : void
    {
        $uri = new Uri('http', null, null, 'host.com', null, '', 'foo=bar', null);
        $this->assertEquals('bar', $this->parser->getQueryStringParam($uri, 'foo'));
    }

    /**
     * Tests that URL-encoded values are decoded
     */
    public function testUrlEncodedValuesAreDecoded() : void
    {
        $uri = new Uri('http', null, null, 'host.com', null, '', 'foo=a%26w', null);
        $this->assertEquals('a&w', $this->parser->getQueryStringParam($uri, 'foo'));
    }
}
