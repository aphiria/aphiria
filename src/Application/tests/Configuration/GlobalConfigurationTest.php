<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Tests\Configuration;

use Aphiria\Application\Configuration\GlobalConfiguration;
use Aphiria\Application\Configuration\HashTableConfiguration;
use Aphiria\Application\Configuration\MissingConfigurationValueException;
use Aphiria\Application\Tests\Configuration\Mocks\ConfigObject;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class GlobalConfigurationTest extends TestCase
{
    protected function setUp(): void
    {
        GlobalConfiguration::resetConfigurationSources();
    }

    public function testAddingMultipleConfigurationSourcesMakesAllValuesReadable(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => 'bar']));
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['baz' => 'blah']));
        $this->assertSame('bar', GlobalConfiguration::getValue('foo'));
        $this->assertSame('blah', GlobalConfiguration::getValue('baz'));
    }

    public function testGetArrayForNestedValueReturnsArray(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => ['bar' => [1, 2]]]));
        $this->assertEquals([1, 2], GlobalConfiguration::getArray('foo.bar'));
    }

    public function testGetArrayReturnsArray(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => [1, 2]]));
        $this->assertEquals([1, 2], GlobalConfiguration::getArray('foo'));
    }

    public function testGetBoolForNestedValueReturnsBool(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => ['bar' => true]]));
        $this->assertTrue(GlobalConfiguration::getBool('foo.bar'));
    }

    public function testGetBoolReturnsBool(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => true]));
        $this->assertTrue(GlobalConfiguration::getBool('foo'));
    }

    public function testGetFloatForNestedValueReturnsFloat(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => ['bar' => 1.2]]));
        $this->assertSame(1.2, GlobalConfiguration::getFloat('foo.bar'));
    }

    public function testGetFloatReturnsFloat(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => 1.2]));
        $this->assertSame(1.2, GlobalConfiguration::getFloat('foo'));
    }

    public function testGetIntForNestedValueReturnsInt(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => ['bar' => 1]]));
        $this->assertSame(1, GlobalConfiguration::getInt('foo.bar'));
    }

    public function testGetIntReturnsInt(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => 1]));
        $this->assertSame(1, GlobalConfiguration::getInt('foo'));
    }

    public function testGetObjectForNestedValueReturnsObject(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => ['bar' => 'baz']]));
        $object = GlobalConfiguration::getObject('foo.bar', fn (mixed $options): ConfigObject => new ConfigObject($options));
        $this->assertEquals(new ConfigObject('baz'), $object);
    }

    public function testGetObjectReturnsObject(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => 'bar']));
        $object = GlobalConfiguration::getObject('foo', fn (mixed $options): ConfigObject => new ConfigObject($options));
        $this->assertEquals(new ConfigObject('bar'), $object);
    }

    public function testGetObjectThrowsExceptionIfFactoryDoesNotReturnObject(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Factory must return an object');
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => 'bar']));
        /** @psalm-suppress InvalidArgument Purposely testing an invalid parameter */
        GlobalConfiguration::getObject('foo', fn (mixed $options): bool => true);
    }

    public function testGetStringForNestedValueReturnsString(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => ['bar' => 'baz']]));
        $this->assertSame('baz', GlobalConfiguration::getString('foo.bar'));
    }

    public function testGetStringReturnsString(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => 'bar']));
        $this->assertSame('bar', GlobalConfiguration::getString('foo'));
    }

    public function testGetValueFallsBackToAnotherSourceIfTheFirstOneDoesNotHaveIt(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => 'bar']));
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['baz' => 'blah']));
        $this->assertSame('blah', GlobalConfiguration::getValue('baz'));
    }

    public function testGetValueForNestedPathReturnsValue(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => ['bar' => ['baz' => 'blah']]]));
        $this->assertSame('blah', GlobalConfiguration::getValue('foo.bar.baz'));
    }

    public function testGetValueForNonExistentNestedPathThrowsException(): void
    {
        $this->expectException(MissingConfigurationValueException::class);
        $this->expectExceptionMessage('No configuration value at foo.blah');
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => ['bar' => 'baz']]));
        GlobalConfiguration::getValue('foo.blah');
    }

    public function testGetValueForNonExistentTopLevelPathThrowsException(): void
    {
        $this->expectException(MissingConfigurationValueException::class);
        $this->expectExceptionMessage('No configuration value at baz');
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => 'bar']));
        GlobalConfiguration::getValue('baz');
    }

    #[RunInSeparateProcess]
    public function testGetValueWithoutSettingConfigurationSourceThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No source configurations set');
        GlobalConfiguration::getValue('foo');
    }

    public function testTryGetArrayForExistentValueSetsItAndReturnsTrue(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => ['bar' => 'baz']]));
        $value = null;
        $this->assertTrue(GlobalConfiguration::tryGetArray('foo', $value));
        $this->assertEquals(['bar' => 'baz'], $value);
    }

    public function testTryGetArrayForNonExistentValueSetsItToNullAndReturnsFalse(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => ['bar' => 'baz']]));
        $value = null;
        $this->assertFalse(GlobalConfiguration::tryGetArray('doesNotExist', $value));
        $this->assertNull($value);
    }

    public function testTryGetBoolForExistentValueSetsItAndReturnsTrue(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => false]));
        $value = null;
        $this->assertTrue(GlobalConfiguration::tryGetBool('foo', $value));
        $this->assertFalse($value);
    }

    public function testTryGetBoolForNonExistentValueSetsItToNullAndReturnsFalse(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => ['bar' => 'baz']]));
        $value = null;
        $this->assertFalse(GlobalConfiguration::tryGetBool('doesNotExist', $value));
        $this->assertNull($value);
    }

    public function testTryGetFloatForExistentValueSetsItAndReturnsTrue(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => 1.2]));
        $value = null;
        $this->assertTrue(GlobalConfiguration::tryGetFloat('foo', $value));
        $this->assertSame(1.2, $value);
    }

    public function testTryGetFloatForNonExistentValueSetsItToNullAndReturnsFalse(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => ['bar' => 'baz']]));
        $value = null;
        $this->assertFalse(GlobalConfiguration::tryGetFloat('doesNotExist', $value));
        $this->assertNull($value);
    }

    public function testTryGetIntForExistentValueSetsItAndReturnsTrue(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => 1]));
        $value = null;
        $this->assertTrue(GlobalConfiguration::tryGetInt('foo', $value));
        $this->assertSame(1, $value);
    }

    public function testTryGetIntForNonExistentValueSetsItToNullAndReturnsFalse(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => ['bar' => 'baz']]));
        $value = null;
        $this->assertFalse(GlobalConfiguration::tryGetInt('doesNotExist', $value));
        $this->assertNull($value);
    }

    public function testTryGetObjectForExistentValueSetsItAndReturnsTrue(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => 'bar']));
        $object = null;
        $this->assertTrue(
            GlobalConfiguration::tryGetObject(
                'foo',
                fn (mixed $options): ConfigObject => new ConfigObject($options),
                $object
            )
        );
        $this->assertEquals(new ConfigObject('bar'), $object);
    }

    public function testTryGetObjectForNonExistentValueSetsItToNullAndReturnsFalse(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => 'bar']));
        /** @var ConfigObject|null $object */
        $object = null;
        $this->assertFalse(
            GlobalConfiguration::tryGetObject(
                'baz',
                fn (mixed $options): ConfigObject => new ConfigObject($options),
                $object
            )
        );
        /** @psalm-suppress DocblockTypeContradiction This should be perfectly valid - bug */
        $this->assertNull($object);
    }

    public function testTryGetObjectThrowsExceptionIfFactoryDoesNotReturnObject(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Factory must return an object');
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => 'bar']));
        $object = null;
        /** @psalm-suppress InvalidArgument Purposely testing an invalid parameter */
        GlobalConfiguration::tryGetObject('foo', fn (mixed $options): bool => true, $object);
    }

    public function testTryGetStringForExistentValueSetsItAndReturnsTrue(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => 'bar']));
        $value = null;
        $this->assertTrue(GlobalConfiguration::tryGetString('foo', $value));
        $this->assertSame('bar', $value);
    }

    public function testTryGetStringForNonExistentValueSetsItToNullAndReturnsFalse(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => ['bar' => 'baz']]));
        $value = null;
        $this->assertFalse(GlobalConfiguration::tryGetString('doesNotExist', $value));
        $this->assertNull($value);
    }

    public function testTryGetValueForExistentValueSetsItAndReturnsTrue(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => 'bar']));
        $value = null;
        $this->assertTrue(GlobalConfiguration::tryGetValue('foo', $value));
        $this->assertSame('bar', $value);
    }

    public function testTryGetValueForNonExistentValueSetsItToNullAndReturnsFalse(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => ['bar' => 'baz']]));
        $value = null;
        $this->assertFalse(GlobalConfiguration::tryGetValue('doesNotExist', $value));
        $this->assertNull($value);
    }
}
