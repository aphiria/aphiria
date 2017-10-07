<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http;

/**
 * Tests the HTTP headers
 */
class HttpHeadersTest extends \PHPUnit\Framework\TestCase
{
    /** @var HttpHeaders The headers to use */
    private $headers = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->headers = new HttpHeaders();
    }

    /**
     * Tests setting a string value
     */
    public function testAddingStringValue()
    {
        $this->headers->add('foo', 'bar');
        $this->assertEquals('bar', $this->headers->get('foo'));
    }

    /**
     * Tests checking if a header exists
     */
    public function testCheckingIfHeaderExists() : void
    {
        $this->assertFalse($this->headers->has('foo'));
        $this->headers->add('foo', 'bar');
        $this->assertTrue($this->headers->has('foo'));
    }

    /**
     * Tests getting all values
     */
    public function testGettingAll() : void
    {
        $this->headers->add('foo', 'bar');
        $this->assertEquals(['Foo' => ['bar']], $this->headers->getAll());
    }

    /**
     * Tests getting all values for a header returns a list of values
     */
    public function testGettingAllValuesForHeaderReturnsListOfValues()
    {
        $this->headers->add('foo', ['bar', 'baz']);
        $this->assertEquals(['bar', 'baz'], $this->headers->get('foo', null, false));
    }

    /**
     * Tests returning only the first value
     */
    public function testGettingFirstValue()
    {
        $this->headers->add('foo', ['bar', 'baz']);
        $this->assertEquals('bar', $this->headers->get('foo', null, true));
    }

    /**
     * Tests returning only the first value when the key does not exist
     */
    public function testGettingFirstValueWhenKeyDoesNotExist()
    {
        $this->assertEquals('foo', $this->headers->get('THIS_DOES_NOT_EXIST', 'foo', true));
    }

    /**
     * Tests that getting the parameters returns an array with a value if no parameters exist
     */
    public function testGettingParametersReturnsArrayWithValueIfNoParametersExist() : void
    {
        $this->headers->add('Foo', 'bar');
        $this->assertEquals(['bar' => null], $this->headers->getParameters('Foo')->getAll());
    }

    /**
     * Tests that getting the parameters returns null if the header does not exist
     */
    public function testGettingParametersReturnsNullIfHeaderDoesNotExist() : void
    {
        $this->assertEquals(0, $this->headers->getParameters('Does-Not-Exist')->count());
    }

    /**
     * Tests that getting the parameters with a mix of value and value-less parameters returns correct parameters
     */
    public function testGettingParametersWithMixOfValueAndValueLessParametersReturnsCorrectParameters() : void
    {
        $this->headers->add('Foo', 'bar; baz="blah"');
        $this->assertEquals(['bar' => null, 'baz' => 'blah'], $this->headers->getParameters('Foo')->getAll());
    }

    /**
     * Tests that getting the parameters of a header with multiple values returns an array of parameters
     */
    public function testGettingParametersWithMultipleValuesReturnsArrayOfParameters() : void
    {
        $expectedParameters = [['foo' => null, 'baz' => 'blah'], ['dave' => 'young', 'alex' => null]];
        $this->headers->add('test', 'foo; baz=blah');
        $this->headers->add('test', 'dave=young; alex', true);
        $actualParameters = $this->headers->getParameters('test', false);
        $this->assertCount(2, $actualParameters);
        $this->assertEquals($expectedParameters[0], $actualParameters[0]->getAll());
        $this->assertEquals($expectedParameters[1], $actualParameters[1]->getAll());
    }

    /**
     * Tests getting parameters with quoted and unquoted values returns an array with the unquoted value
     */
    public function testGettingParametersWithQuotedAndUnquotedValuesReturnsArrayWithUnquotedValue() : void
    {
        $this->headers->add('Foo', 'bar=baz');
        $this->assertEquals(['bar' => 'baz'], $this->headers->getParameters('Foo')->getAll());
        $this->headers->remove('Foo');
        $this->headers->add('Foo', 'bar="baz"');
        $this->assertEquals(['bar' => 'baz'], $this->headers->getParameters('Foo')->getAll());
    }

    /**
     * Tests that all names are normalized
     */
    public function testNamesAreNormalized() : void
    {
        // Test lower-case names
        $this->headers->add('foo', 'bar');
        $this->assertEquals('bar', $this->headers->get('foo'));
        $this->assertEquals(['Foo' => ['bar']], $this->headers->getAll());
        $this->assertTrue($this->headers->has('foo'));
        $this->headers->remove('foo');
        // Test snake-case names
        $this->headers->add('FOO_BAR', 'baz');
        $this->assertEquals('baz', $this->headers->get('FOO_BAR'));
        $this->assertEquals(['Foo-Bar' => ['baz']], $this->headers->getAll());
        $this->assertTrue($this->headers->has('FOO_BAR'));
        $this->headers->remove('FOO_BAR');
        // Test upper-case names
        $this->assertEquals([], $this->headers->getAll());
        $this->headers->add('BAZ', 'blah');
        $this->assertEquals('blah', $this->headers->get('BAZ'));
        $this->assertEquals(['Baz' => ['blah']], $this->headers->getAll());
        $this->assertTrue($this->headers->has('BAZ'));
        $this->headers->remove('BAZ');
        $this->assertEquals([], $this->headers->getAll());
    }

    /**
     * Tests removing a header
     */
    public function testRemovingHeader() : void
    {
        $this->headers->add('foo', 'bar');
        $this->headers->remove('foo');
        $this->assertFalse($this->headers->has('foo'));
    }

    /**
     * Tests setting a header and appending it appends it
     */
    public function testSettingHeaderAndAppendingItAppendsIt() : void
    {
        $this->headers->add('foo', 'bar');
        $this->headers->add('foo', 'baz', true);
        $this->assertEquals(['bar', 'baz'], $this->headers->get('foo', null, false));
    }

    /**
     * Tests setting a header without appending it appends it
     */
    public function testSettingHeaderWithoutAppendingReplacesIt() : void
    {
        $this->headers->add('foo', 'bar');
        $this->headers->add('foo', 'baz', false);
        $this->assertEquals(['baz'], $this->headers->get('foo', null, false));
    }
}
