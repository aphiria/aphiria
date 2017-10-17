<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http;

use Opulence\Net\Http\HttpHeaderParser;
use Opulence\Net\Http\HttpHeaders;

/**
 * Tests the HTTP header parser
 */
class HttpHeaderParserTest extends \PHPUnit\Framework\TestCase
{
    /** @var HttpHeaderParser The parser to use in tests */
    private $parser = null;
    /** @var HttpHeaders The headers to use in tests */
    private $headers = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->parser = new HttpHeaderParser();
        $this->headers = new HttpHeaders();
    }

    /**
     * Tests that getting the parameters returns an array with a value if no parameters exist
     */
    public function testGettingParametersReturnsArrayWithValueIfNoParametersExist() : void
    {
        $this->headers->add('Foo', 'bar');
        $this->assertNull($this->parser->parseParametersForFirstValue($this->headers, 'Foo')->get('bar'));
    }

    /**
     * Tests that getting the parameters returns null if the header does not exist
     */
    public function testGettingParametersReturnsNullIfHeaderDoesNotExist() : void
    {
        $this->assertEquals(0, $this->parser->parseParametersForFirstValue($this->headers, 'Does-Not-Exist')->count());
    }

    /**
     * Tests that getting the parameters with a mix of value and value-less parameters returns correct parameters
     */
    public function testGettingParametersWithMixOfValueAndValueLessParametersReturnsCorrectParameters() : void
    {
        $this->headers->add('Foo', 'bar; baz="blah"');
        $values = $this->parser->parseParametersForFirstValue($this->headers, 'Foo');
        $this->assertNull($values->get('bar'));
        $this->assertEquals('blah', $values->get('baz'));
    }

    /**
     * Tests that getting the parameters of a header with multiple values returns an array of parameters
     */
    public function testGettingParametersWithMultipleValuesReturnsArrayOfParameters() : void
    {
        $this->headers->add('test', 'foo; baz=blah');
        $this->headers->add('test', 'dave=young; alex', true);
        $actualParameters = $this->parser->parseParametersForAllValues($this->headers, 'test');
        $this->assertCount(2, $actualParameters);
        $this->assertNull($actualParameters[0]->get('foo'));
        $this->assertEquals('blah', $actualParameters[0]->get('baz'));
        $this->assertEquals('young', $actualParameters[1]->get('dave'));
        $this->assertNull($actualParameters[1]->get('alex'));
    }

    /**
     * Tests getting parameters with quoted and unquoted values returns an array with the unquoted value
     */
    public function testGettingParametersWithQuotedAndUnquotedValuesReturnsArrayWithUnquotedValue() : void
    {
        $this->headers->add('Foo', 'bar=baz');
        $this->assertEquals('baz', $this->parser->parseParametersForFirstValue($this->headers, 'Foo')->get('bar'));
        $this->headers->removeKey('Foo');
        $this->headers->add('Foo', 'bar="baz"');
        $this->assertEquals('baz', $this->parser->parseParametersForFirstValue($this->headers, 'Foo')->get('bar'));
    }
}
