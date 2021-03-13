<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Tests\Configuration;

use Aphiria\Application\Configuration\HashTableConfiguration;
use Aphiria\Application\Configuration\MissingConfigurationValueException;
use Aphiria\Application\Tests\Configuration\Mocks\ConfigObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class HashTableConfigurationTest extends TestCase
{
    public function testGetArrayForNestedValueReturnsArray(): void
    {
        $configuration = new HashTableConfiguration(['foo' => ['bar' => [1, 2]]]);
        $this->assertEquals([1, 2], $configuration->getArray('foo.bar'));
    }

    public function testGetArrayReturnsArray(): void
    {
        $configuration = new HashTableConfiguration(['foo' => [1, 2]]);
        $this->assertEquals([1, 2], $configuration->getArray('foo'));
    }

    public function testGetBoolForNestedValueReturnsBool(): void
    {
        $configuration = new HashTableConfiguration(['foo' => ['bar' => true]]);
        $this->assertTrue($configuration->getBool('foo.bar'));
    }

    public function testGetBoolReturnsBool(): void
    {
        $configuration = new HashTableConfiguration(['foo' => true]);
        $this->assertTrue($configuration->getBool('foo'));
    }

    public function testGetFloatForNestedValueReturnsFloat(): void
    {
        $configuration = new HashTableConfiguration(['foo' => ['bar' => 1.2]]);
        $this->assertSame(1.2, $configuration->getFloat('foo.bar'));
    }

    public function testGetFloatReturnsFloat(): void
    {
        $configuration = new HashTableConfiguration(['foo' => 1.2]);
        $this->assertSame(1.2, $configuration->getFloat('foo'));
    }

    public function testGetIntForNestedValueReturnsInt(): void
    {
        $configuration = new HashTableConfiguration(['foo' => ['bar' => 1]]);
        $this->assertSame(1, $configuration->getInt('foo.bar'));
    }

    public function testGetIntReturnsInt(): void
    {
        $configuration = new HashTableConfiguration(['foo' => 1]);
        $this->assertSame(1, $configuration->getInt('foo'));
    }

    public function testGetObjectForNestedValueReturnsObject(): void
    {
        $configuration = new HashTableConfiguration(['foo' => ['bar' => 'baz']]);
        $object = $configuration->getObject('foo.bar', fn (mixed $options): ConfigObject => new ConfigObject($options));
        $this->assertEquals(new ConfigObject('baz'), $object);
    }

    public function testGetObjectReturnsObject(): void
    {
        $configuration = new HashTableConfiguration(['foo' => 'bar']);
        $object = $configuration->getObject('foo', fn (mixed $options): ConfigObject => new ConfigObject($options));
        $this->assertEquals(new ConfigObject('bar'), $object);
    }

    public function testGetObjectThrowsExceptionIfFactoryDoesNotReturnObject(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Factory must return an object');
        $configuration = new HashTableConfiguration(['foo' => 'bar']);
        /** @psalm-suppress InvalidArgument Purposely testing an invalid parameter */
        $configuration->getObject('foo', fn (mixed $options): bool => true);
    }

    public function testGetStringForNestedValueReturnsString(): void
    {
        $configuration = new HashTableConfiguration(['foo' => ['bar' => 'baz']]);
        $this->assertSame('baz', $configuration->getString('foo.bar'));
    }

    public function testGetStringReturnsString(): void
    {
        $configuration = new HashTableConfiguration(['foo' => 'bar']);
        $this->assertSame('bar', $configuration->getString('foo'));
    }

    public function testGetValueForNestedPathReturnsValue(): void
    {
        $configuration = new HashTableConfiguration(['foo' => ['bar' => ['baz' => 'blah']]]);
        $this->assertSame('blah', $configuration->getValue('foo.bar.baz'));
    }

    public function testGetValueForNonExistentNestedPathThrowsException(): void
    {
        $this->expectException(MissingConfigurationValueException::class);
        $this->expectExceptionMessage('No configuration value at foo.blah');
        $configuration = new HashTableConfiguration(['foo' => ['bar' => 'baz']]);
        $configuration->getValue('foo.blah');
    }

    public function testGetValueForNonExistentTopLevelPathThrowsException(): void
    {
        $this->expectException(MissingConfigurationValueException::class);
        $this->expectExceptionMessage('No configuration value at baz');
        $configuration = new HashTableConfiguration(['foo' => 'bar']);
        $configuration->getValue('baz');
    }

    public function testGetValueRespectsCustomPathDelimitersForNestedPathSegments(): void
    {
        // We're specifically testing multiple levels deep to test the top level of keys and further down levels
        $configuration = new HashTableConfiguration(['foo' => ['bar' => ['baz' => 'blah']]], ':');
        $this->assertSame('blah', $configuration->getValue('foo:bar:baz'));
    }

    public function testTryGetArrayForExistentValueSetsItAndReturnsTrue(): void
    {
        $configuration = new HashTableConfiguration(['foo' => ['bar' => 'baz']]);
        $value = null;
        $this->assertTrue($configuration->tryGetArray('foo', $value));
        $this->assertEquals(['bar' => 'baz'], $value);
    }

    public function testTryGetArrayForNonExistentValueSetsItToNullAndReturnsFalse(): void
    {
        $configuration = new HashTableConfiguration(['foo' => ['bar' => 'baz']]);
        $value = null;
        $this->assertFalse($configuration->tryGetArray('doesNotExist', $value));
        $this->assertNull($value);
    }

    public function testTryGetBoolForExistentValueSetsItAndReturnsTrue(): void
    {
        $configuration = new HashTableConfiguration(['foo' => false]);
        $value = null;
        $this->assertTrue($configuration->tryGetBool('foo', $value));
        $this->assertFalse($value);
    }

    public function testTryGetBoolForNonExistentValueSetsItToNullAndReturnsFalse(): void
    {
        $configuration = new HashTableConfiguration(['foo' => ['bar' => 'baz']]);
        $value = null;
        $this->assertFalse($configuration->tryGetBool('doesNotExist', $value));
        $this->assertNull($value);
    }

    public function testTryGetFloatForExistentValueSetsItAndReturnsTrue(): void
    {
        $configuration = new HashTableConfiguration(['foo' => 1.2]);
        $value = null;
        $this->assertTrue($configuration->tryGetFloat('foo', $value));
        $this->assertSame(1.2, $value);
    }

    public function testTryGetFloatForNonExistentValueSetsItToNullAndReturnsFalse(): void
    {
        $configuration = new HashTableConfiguration(['foo' => ['bar' => 'baz']]);
        $value = null;
        $this->assertFalse($configuration->tryGetFloat('doesNotExist', $value));
        $this->assertNull($value);
    }

    public function testTryGetIntForExistentValueSetsItAndReturnsTrue(): void
    {
        $configuration = new HashTableConfiguration(['foo' => 1]);
        $value = null;
        $this->assertTrue($configuration->tryGetInt('foo', $value));
        $this->assertSame(1, $value);
    }

    public function testTryGetIntForNonExistentValueSetsItToNullAndReturnsFalse(): void
    {
        $configuration = new HashTableConfiguration(['foo' => ['bar' => 'baz']]);
        $value = null;
        $this->assertFalse($configuration->tryGetInt('doesNotExist', $value));
        $this->assertNull($value);
    }

    public function testTryGetObjectForExistentValueSetsItAndReturnsTrue(): void
    {
        $configuration = new HashTableConfiguration(['foo' => 'bar']);
        $object = null;
        $this->assertTrue(
            $configuration->tryGetObject(
                'foo',
                fn (mixed $options): ConfigObject => new ConfigObject($options),
                $object
            )
        );
        $this->assertEquals(new ConfigObject('bar'), $object);
    }

    public function testTryGetObjectForNonExistentValueSetsItToNullAndReturnsFalse(): void
    {
        $configuration = new HashTableConfiguration(['foo' => 'bar']);
        $object = null;
        $this->assertFalse(
            $configuration->tryGetObject(
                'baz',
                fn (mixed $options): ConfigObject => new ConfigObject($options),
                $object
            )
        );
        $this->assertNull($object);
    }

    public function testTryGetObjectThrowsExceptionIfFactoryDoesNotReturnObject(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Factory must return an object');
        $configuration = new HashTableConfiguration(['foo' => 'bar']);
        $object = null;
        /** @psalm-suppress InvalidArgument Purposely testing an invalid parameter */
        $configuration->tryGetObject('foo', fn (mixed $options): bool => true, $object);
    }

    public function testTryGetStringForExistentValueSetsItAndReturnsTrue(): void
    {
        $configuration = new HashTableConfiguration(['foo' => 'bar']);
        $value = null;
        $this->assertTrue($configuration->tryGetString('foo', $value));
        $this->assertSame('bar', $value);
    }

    public function testTryGetStringForNonExistentValueSetsItToNullAndReturnsFalse(): void
    {
        $configuration = new HashTableConfiguration(['foo' => ['bar' => 'baz']]);
        $value = null;
        $this->assertFalse($configuration->tryGetString('doesNotExist', $value));
        $this->assertNull($value);
    }

    public function testTryGetValueForExistentValueSetsItAndReturnsTrue(): void
    {
        $configuration = new HashTableConfiguration(['foo' => 'bar']);
        $value = null;
        $this->assertTrue($configuration->tryGetValue('foo', $value));
        $this->assertSame('bar', $value);
    }

    public function testTryGetValueForNonExistentValueSetsItToNullAndReturnsFalse(): void
    {
        $configuration = new HashTableConfiguration(['foo' => ['bar' => 'baz']]);
        $value = null;
        $this->assertFalse($configuration->tryGetValue('doesNotExist', $value));
        $this->assertNull($value);
    }
}
