<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Configuration\Tests;

use Aphiria\Configuration\HashTableConfiguration;
use Aphiria\Configuration\MissingConfigurationValueException;
use Aphiria\Configuration\GlobalConfiguration;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Tests the global configuration
 */
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
        $this->assertEquals('bar', GlobalConfiguration::getValue('foo'));
        $this->assertEquals('blah', GlobalConfiguration::getValue('baz'));
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
        $this->assertEquals(1.2, GlobalConfiguration::getFloat('foo.bar'));
    }

    public function testGetFloatReturnsFloat(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => 1.2]));
        $this->assertEquals(1.2, GlobalConfiguration::getFloat('foo'));
    }

    public function testGetIntForNestedValueReturnsInt(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => ['bar' => 1]]));
        $this->assertEquals(1, GlobalConfiguration::getInt('foo.bar'));
    }

    public function testGetIntReturnsInt(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => 1]));
        $this->assertEquals(1, GlobalConfiguration::getInt('foo'));
    }

    public function testGetStringForNestedValueReturnsString(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => ['bar' => 'baz']]));
        $this->assertEquals('baz', GlobalConfiguration::getString('foo.bar'));
    }

    public function testGetStringReturnsString(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => 'bar']));
        $this->assertEquals('bar', GlobalConfiguration::getString('foo'));
    }

    public function testGetValueFallsBackToAnotherSourceIfTheFirstOneDoesNotHaveIt(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => 'bar']));
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['baz' => 'blah']));
        $this->assertEquals('blah', GlobalConfiguration::getValue('baz'));
    }

    public function testGetValueForNestedPathReturnsValue(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => ['bar' => ['baz' => 'blah']]]));
        $this->assertEquals('blah', GlobalConfiguration::getValue('foo.bar.baz'));
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

    /**
     * @runInSeparateProcess
     */
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
        $this->assertEquals(1.2, $value);
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
        $this->assertEquals(1, $value);
    }

    public function testTryGetIntForNonExistentValueSetsItToNullAndReturnsFalse(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => ['bar' => 'baz']]));
        $value = null;
        $this->assertFalse(GlobalConfiguration::tryGetInt('doesNotExist', $value));
        $this->assertNull($value);
    }

    public function testTryGetStringForExistentValueSetsItAndReturnsTrue(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => 'bar']));
        $value = null;
        $this->assertTrue(GlobalConfiguration::tryGetString('foo', $value));
        $this->assertEquals('bar', $value);
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
        $this->assertEquals('bar', $value);
    }

    public function testTryGetValueForNonExistentValueSetsItToNullAndReturnsFalse(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['foo' => ['bar' => 'baz']]));
        $value = null;
        $this->assertFalse(GlobalConfiguration::tryGetValue('doesNotExist', $value));
        $this->assertNull($value);
    }
}
