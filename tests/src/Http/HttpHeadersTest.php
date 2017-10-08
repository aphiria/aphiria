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
        $this->assertFalse($this->headers->containsKey('foo'));
        $this->headers->add('foo', 'bar');
        $this->assertTrue($this->headers->containsKey('foo'));
    }

    /**
     * Tests getting all values
     */
    public function testGettingAll() : void
    {
        $this->headers->add('foo', 'bar');
        $this->assertEquals(['Foo' => ['bar']], $this->headers->toArray());
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
        $this->assertEquals(['bar' => null], $this->headers->getParameters('Foo')->toArray());
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
        $this->assertEquals(['bar' => null, 'baz' => 'blah'], $this->headers->getParameters('Foo')->toArray());
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
        $this->assertEquals($expectedParameters[0], $actualParameters[0]->toArray());
        $this->assertEquals($expectedParameters[1], $actualParameters[1]->toArray());
    }

    /**
     * Tests getting parameters with quoted and unquoted values returns an array with the unquoted value
     */
    public function testGettingParametersWithQuotedAndUnquotedValuesReturnsArrayWithUnquotedValue() : void
    {
        $this->headers->add('Foo', 'bar=baz');
        $this->assertEquals(['bar' => 'baz'], $this->headers->getParameters('Foo')->toArray());
        $this->headers->removeKey('Foo');
        $this->headers->add('Foo', 'bar="baz"');
        $this->assertEquals(['bar' => 'baz'], $this->headers->getParameters('Foo')->toArray());
    }

    /**
     * Tests that all names are normalized
     */
    public function testNamesAreNormalized() : void
    {
        // Test lower-case names
        $this->headers->add('foo', 'bar');
        $this->assertEquals('bar', $this->headers->get('foo'));
        $this->assertEquals(['Foo' => ['bar']], $this->headers->toArray());
        $this->assertTrue($this->headers->containsKey('foo'));
        $this->headers->removeKey('foo');
        // Test snake-case names
        $this->headers->add('FOO_BAR', 'baz');
        $this->assertEquals('baz', $this->headers->get('FOO_BAR'));
        $this->assertEquals(['Foo-Bar' => ['baz']], $this->headers->toArray());
        $this->assertTrue($this->headers->containsKey('FOO_BAR'));
        $this->headers->removeKey('FOO_BAR');
        // Test upper-case names
        $this->assertEquals([], $this->headers->toArray());
        $this->headers->add('BAZ', 'blah');
        $this->assertEquals('blah', $this->headers->get('BAZ'));
        $this->assertEquals(['Baz' => ['blah']], $this->headers->toArray());
        $this->assertTrue($this->headers->containsKey('BAZ'));
        $this->headers->removeKey('BAZ');
        $this->assertEquals([], $this->headers->toArray());
    }

    /**
     * Tests removing a header
     */
    public function testRemovingHeader() : void
    {
        $this->headers->add('foo', 'bar');
        $this->headers->removeKey('foo');
        $this->assertFalse($this->headers->containsKey('foo'));
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
