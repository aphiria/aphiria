<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http;

use Aphiria\Collections\KeyValuePair;
use Aphiria\Net\Http\Headers;
use InvalidArgumentException;
use OutOfBoundsException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class HeadersTest extends TestCase
{
    private Headers $headers;

    protected function setUp(): void
    {
        $this->headers = new Headers();
    }

    public static function getInvalidHeaderValues(): array
    {
        $object = new class () {
        };

        return [
            ['foo', $object],
            ['foo', [$object]],
            ['foo', ['bar', $object]]
        ];
    }

    /**
     * @param string $name The name of the header value to test
     * @param mixed $value The header value to test
     */
    #[DataProvider('getInvalidHeaderValues')]
    public function testAddingInvalidValueThrowsException(string $name, mixed $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Header values can only be strings, numbers, or lists of strings or numbers');
        $this->headers->add($name, $value);
    }

    public function testAddingRangeOfInvalidValueThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Header values can only be strings, numbers, or lists of strings or numbers');
        /** @psalm-suppress InvalidArgument Purposely testing invalid values */
        $this->headers->addRange([new KeyValuePair('foo', $this)]);
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
        $this->assertSame('bar', $this->headers->getFirst('foo'));
    }

    public function testGettingFirstValueWhenKeyDoesNotExistThrowsException(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Header "foo" does not exist');
        $this->headers->getFirst('foo');
    }

    public function testNamesAreNormalizedWhenAddingSingleValue(): void
    {
        // Test lower-case names
        $this->headers->add('foo', 'bar');
        $this->assertEquals(['bar'], $this->headers->get('Foo'));
        $this->assertSame('bar', $this->headers->getFirst('foo'));
        $this->assertTrue($this->headers->containsKey('foo'));
        $this->headers->removeKey('foo');
        // Test snake-case names
        $this->headers->add('FOO_BAR', 'baz');
        $this->assertEquals(['baz'], $this->headers->get('Foo-Bar'));
        $this->assertSame('baz', $this->headers->getFirst('FOO_BAR'));
        $this->assertTrue($this->headers->containsKey('FOO_BAR'));
        $this->headers->removeKey('FOO_BAR');
        // Test upper-case names
        $this->assertEquals([], $this->headers->toArray());
        $this->headers->add('BAZ', 'blah');
        $this->assertEquals(['blah'], $this->headers->get('Baz'));
        $this->assertSame('blah', $this->headers->getFirst('BAZ'));
        $this->assertTrue($this->headers->containsKey('BAZ'));
        $this->headers->removeKey('BAZ');
        $this->assertEquals([], $this->headers->toArray());
    }

    public function testNamesAreNormalizedWhenAddingMultipleValue(): void
    {
        // Test lower-case names
        $this->headers->addRange([new KeyValuePair('foo', 'bar')]);
        $this->assertEquals(['bar'], $this->headers->get('Foo'));
        $this->assertSame('bar', $this->headers->getFirst('foo'));
        $this->assertTrue($this->headers->containsKey('foo'));
        $this->headers->removeKey('foo');
        // Test snake-case names
        $this->headers->addRange([new KeyValuePair('FOO_BAR', 'baz')]);
        $this->assertEquals(['baz'], $this->headers->get('Foo-Bar'));
        $this->assertSame('baz', $this->headers->getFirst('FOO_BAR'));
        $this->assertTrue($this->headers->containsKey('FOO_BAR'));
        $this->headers->removeKey('FOO_BAR');
        // Test upper-case names
        $this->assertEquals([], $this->headers->toArray());
        $this->headers->addRange([new KeyValuePair('BAZ', 'blah')]);
        $this->assertEquals(['blah'], $this->headers->get('Baz'));
        $this->assertSame('blah', $this->headers->getFirst('BAZ'));
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
        $this->assertSame('Foo: bar, baz', (string)$this->headers);
    }

    public function testSerializingSplitsHeadersIntoLines(): void
    {
        $this->headers->add('Foo', 'bar');
        $this->headers->add('Baz', 'blah');
        $this->assertSame("Foo: bar\r\nBaz: blah", (string)$this->headers);
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
        $this->headers->add('foo', 'baz');
        $this->assertEquals(['baz'], $this->headers->get('foo'));
    }

    public function testToArrayReturnsListOfKeyValuePairs(): void
    {
        $this->headers->add('foo', 'bar');
        $actualValues = [];

        /** @var KeyValuePair $value */
        foreach ($this->headers->toArray() as $key => $value) {
            // Verify that the key is numeric, not associative
            /** @psalm-suppress RedundantCondition We do not want to rely only on Psalm's type checking */
            $this->assertIsInt($key);
            $this->assertInstanceOf(KeyValuePair::class, $value);
            /** @psalm-suppress MixedArrayOffset We're purposely accessing mixed keys */
            $actualValues[$value->key] = $value->value;
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
        $this->assertSame('bar', $value);
    }

    public function testAddRangeOnInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('Value must be instance of %s', KeyValuePair::class));
        /** @psalm-suppress InvalidArgument Purposely checking an invalid argument */
        $this->headers->addRange(['invalid KeyValuePair']);
    }
}
