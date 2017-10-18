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

/**
 * Tests the HTTP header parser
 */
class HttpHeaderParserTest extends \PHPUnit\Framework\TestCase
{
    /** @var HttpHeaderParser The parser to use in tests */
    private $parser = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->parser = new HttpHeaderParser();
    }

    /**
     * Tests that getting the parameters with a mix of value and value-less parameters returns correct parameters
     */
    public function testGettingParametersWithMixOfValueAndValueLessParametersReturnsCorrectParameters() : void
    {
        $values = $this->parser->parseParameters('bar; baz="blah"');
        $this->assertNull($values->get('bar'));
        $this->assertEquals('blah', $values->get('baz'));
    }

    /**
     * Tests getting parameters with quoted and unquoted values returns an array with the unquoted value
     */
    public function testGettingParametersWithQuotedAndUnquotedValuesReturnsArrayWithUnquotedValue() : void
    {
        $this->assertEquals('baz', $this->parser->parseParameters('bar=baz')->get('bar'));
        $this->assertEquals('baz', $this->parser->parseParameters('bar="baz"')->get('bar'));
    }
}
