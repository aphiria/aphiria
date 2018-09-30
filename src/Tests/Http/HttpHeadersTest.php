<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http;

use Opulence\Collections\KeyValuePair;
use Opulence\Net\Http\HttpHeaders;
use OutOfBoundsException;
use InvalidArgumentException;

/**
 * Tests the HTTP headers
 */
class HttpHeadersTest extends \PHPUnit\Framework\TestCase
{
    /** @var HttpHeaders The headers to use */
    private $headers;

    public function setUp(): void
    {
        $this->headers = new HttpHeaders();
    }

    public function testAddingStringValue(): void
    {
        $this->headers->add('foo', 'bar');
        $this->assertEquals(['bar'], $this->headers->get('foo'));
    }

    public function testCheckingIfHeaderExists(): void
    {
        $this->assertFalse($this->headers->containsKey('foo'));
        $this->headers->add('foo', 'bar');
        $this->assertTrue($this->headers->containsKey('foo'));
    }

    public function testGettingAllValuesForHeaderReturnsListOfValues(): void
    {
        $this->headers->add('foo', ['bar', 'baz']);
        $this->assertEquals(['bar', 'baz'], $this->headers->get('foo'));
    }

    public function testGettingFirstValue(): void
    {
        $this->headers->add('foo', ['bar', 'baz']);
        $this->assertEquals('bar', $this->headers->getFirst('foo'));
    }

    public function testGettingFirstValueWhenKeyDoesNotExistThrowsException(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->headers->getFirst('foo');
    }

    public function testNamesAreNormalizedWhenAddingSingleValue(): void
    {
        // Test lower-case names
        $this->headers->add('foo', 'bar');
        $this->assertEquals(['bar'], $this->headers->get('Foo'));
        $this->assertEquals('bar', $this->headers->getFirst('foo'));
        $this->assertTrue($this->headers->containsKey('foo'));
        $this->headers->removeKey('foo');
        // Test snake-case names
        $this->headers->add('FOO_BAR', 'baz');
        $this->assertEquals(['baz'], $this->headers->get('Foo-Bar'));
        $this->assertEquals('baz', $this->headers->getFirst('FOO_BAR'));
        $this->assertTrue($this->headers->containsKey('FOO_BAR'));
        $this->headers->removeKey('FOO_BAR');
        // Test upper-case names
        $this->assertEquals([], $this->headers->toArray());
        $this->headers->add('BAZ', 'blah');
        $this->assertEquals(['blah'], $this->headers->get('Baz'));
        $this->assertEquals('blah', $this->headers->getFirst('BAZ'));
        $this->assertTrue($this->headers->containsKey('BAZ'));
        $this->headers->removeKey('BAZ');
        $this->assertEquals([], $this->headers->toArray());
    }

    public function testNamesAreNormalizedWhenAddingMultipleValue(): void
    {
        // Test lower-case names
        $this->headers->addRange([new KeyValuePair('foo', 'bar')]);
        $this->assertEquals(['bar'], $this->headers->get('Foo'));
        $this->assertEquals('bar', $this->headers->getFirst('foo'));
        $this->assertTrue($this->headers->containsKey('foo'));
        $this->headers->removeKey('foo');
        // Test snake-case names
        $this->headers->addRange([new KeyValuePair('FOO_BAR', 'baz')]);
        $this->assertEquals(['baz'], $this->headers->get('Foo-Bar'));
        $this->assertEquals('baz', $this->headers->getFirst('FOO_BAR'));
        $this->assertTrue($this->headers->containsKey('FOO_BAR'));
        $this->headers->removeKey('FOO_BAR');
        // Test upper-case names
        $this->assertEquals([], $this->headers->toArray());
        $this->headers->addRange([new KeyValuePair('BAZ', 'blah')]);
        $this->assertEquals(['blah'], $this->headers->get('Baz'));
        $this->assertEquals('blah', $this->headers->getFirst('BAZ'));
        $this->assertTrue($this->headers->containsKey('BAZ'));
        $this->headers->removeKey('BAZ');
        $this->assertEquals([], $this->headers->toArray());
    }

    public function testRemovingHeader(): void
    {
        $this->headers->add('foo', 'bar');
        $this->headers->removeKey('foo');
        $this->assertFalse($this->headers->containsKey('foo'));
    }

    public function testSerializingHeadersWithMultipleValuesSplitsTheValuesWithCommas(): void
    {
        $this->headers->add('Foo', 'bar');
        $this->headers->add('Foo', 'baz', true);
        $this->assertEquals('Foo: bar, baz', (string)$this->headers);
    }

    public function testSerializingSplitsHeadersIntoLines(): void
    {
        $this->headers->add('Foo', 'bar');
        $this->headers->add('Baz', 'blah');
        $this->assertEquals("Foo: bar\r\nBaz: blah", (string)$this->headers);
    }

    public function testSettingHeaderAndAppendingItAppendsIt(): void
    {
        $this->headers->add('foo', 'bar');
        $this->headers->add('foo', 'baz', true);
        $this->assertEquals(['bar', 'baz'], $this->headers->get('foo'));
    }

    public function testSettingHeaderWithoutAppendingReplacesIt(): void
    {
        $this->headers->add('foo', 'bar');
        $this->headers->add('foo', 'baz', false);
        $this->assertEquals(['baz'], $this->headers->get('foo'));
    }

    public function testToArrayReturnsListOfKeyValuePairs(): void
    {
        $this->headers->add('foo', 'bar');
        $actualValues = [];

        /**
         * @var int $key
         * @var KeyValuePair $value
         */
        foreach ($this->headers->toArray() as $key => $value) {
            // Verify that the key is numeric, not associative
            $this->assertTrue(\is_int($key));
            $this->assertInstanceOf(KeyValuePair::class, $value);
            $actualValues[$value->getKey()] = $value->getValue();
        }

        $this->assertCount(1, $actualValues);
        // The header name will be normalized
        $this->assertEquals(['bar'], $actualValues['Foo']);
    }

    public function testTryGetFirstReturnsTrueIfKeyExistsOtherwiseFalse(): void
    {
        $value = null;
        $this->assertFalse($this->headers->tryGetFirst('foo', $value));
        $this->headers->add('foo', 'bar');
        $this->assertTrue($this->headers->tryGetFirst('foo', $value));
        $this->assertEquals('bar', $value);
    }

    public function testAddRangeOnInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->headers->addRange(['invalid KeyValuePair']);
    }
}
