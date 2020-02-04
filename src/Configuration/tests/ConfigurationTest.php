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

use Aphiria\Configuration\Configuration;
use Aphiria\Configuration\ConfigurationException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Tests the configuration
 */
class ConfigurationTest extends TestCase
{
    public function testGetArrayForNestedValueReturnsArray(): void
    {
        new Configuration(['foo' => ['bar' => [1, 2]]]);
        $this->assertEquals([1, 2], Configuration::getArray('foo.bar'));
    }

    public function testGetArrayReturnsArray(): void
    {
        new Configuration(['foo' => [1, 2]]);
        $this->assertEquals([1, 2], Configuration::getArray('foo'));
    }

    public function testGetBoolForNestedValueReturnsBool(): void
    {
        new Configuration(['foo' => ['bar' => true]]);
        $this->assertTrue(Configuration::getBool('foo.bar'));
    }

    public function testGetBoolReturnsBool(): void
    {
        new Configuration(['foo' => true]);
        $this->assertTrue(Configuration::getBool('foo'));
    }

    public function testGetFloatForNestedValueReturnsFloat(): void
    {
        new Configuration(['foo' => ['bar' => 1.2]]);
        $this->assertEquals(1.2, Configuration::getFloat('foo.bar'));
    }

    public function testGetFloatReturnsFloat(): void
    {
        new Configuration(['foo' => 1.2]);
        $this->assertEquals(1.2, Configuration::getFloat('foo'));
    }

    public function testGetIntForNestedValueReturnsInt(): void
    {
        new Configuration(['foo' => ['bar' => 1]]);
        $this->assertEquals(1, Configuration::getInt('foo.bar'));
    }

    public function testGetIntReturnsInt(): void
    {
        new Configuration(['foo' => 1]);
        $this->assertEquals(1, Configuration::getInt('foo'));
    }

    public function testGetStringForNestedValueReturnsString(): void
    {
        new Configuration(['foo' => ['bar' => 'baz']]);
        $this->assertEquals('baz', Configuration::getString('foo.bar'));
    }

    public function testGetStringReturnsString(): void
    {
        new Configuration(['foo' => 'bar']);
        $this->assertEquals('bar', Configuration::getString('foo'));
    }

    public function testGetValueForNestedPathReturnsValue(): void
    {
        new Configuration(['foo' => ['bar' => ['baz' => 'blah']]]);
        $this->assertEquals('blah', Configuration::getValue('foo.bar.baz'));
    }

    public function testGetValueForNonExistentNestedPathThrowsException(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('No configuration value at foo.blah');
        new Configuration(['foo' => ['bar' => 'baz']]);
        Configuration::getValue('foo.blah');
    }

    public function testGetValueForNonExistentTopLevelPathThrowsException(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('No configuration value at baz');
        new Configuration(['foo' => 'bar']);
        Configuration::getValue('baz');
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetValueWithoutInstantiatingConfigurationThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Must call ' . Configuration::class . '::__construct() before calling get()');
        Configuration::getValue('foo');
    }

    public function testTryGetArrayForExistentValueSetsItAndReturnsTrue(): void
    {
        new Configuration(['foo' => ['bar' => 'baz']]);
        $value = null;
        $this->assertTrue(Configuration::tryGetArray('foo', $value));
        $this->assertEquals(['bar' => 'baz'], $value);
    }

    public function testTryGetArrayForNonExistentValueSetsItToNullAndReturnsFalse(): void
    {
        new Configuration(['foo' => ['bar' => 'baz']]);
        $value = null;
        $this->assertFalse(Configuration::tryGetArray('doesNotExist', $value));
        $this->assertNull($value);
    }

    public function testTryGetBoolForExistentValueSetsItAndReturnsTrue(): void
    {
        new Configuration(['foo' => false]);
        $value = null;
        $this->assertTrue(Configuration::tryGetBool('foo', $value));
        $this->assertFalse($value);
    }

    public function testTryGetBoolForNonExistentValueSetsItToNullAndReturnsFalse(): void
    {
        new Configuration(['foo' => ['bar' => 'baz']]);
        $value = null;
        $this->assertFalse(Configuration::tryGetBool('doesNotExist', $value));
        $this->assertNull($value);
    }

    public function testTryGetFloatForExistentValueSetsItAndReturnsTrue(): void
    {
        new Configuration(['foo' => 1.2]);
        $value = null;
        $this->assertTrue(Configuration::tryGetFloat('foo', $value));
        $this->assertEquals(1.2, $value);
    }

    public function testTryGetFloatForNonExistentValueSetsItToNullAndReturnsFalse(): void
    {
        new Configuration(['foo' => ['bar' => 'baz']]);
        $value = null;
        $this->assertFalse(Configuration::tryGetFloat('doesNotExist', $value));
        $this->assertNull($value);
    }

    public function testTryGetIntForExistentValueSetsItAndReturnsTrue(): void
    {
        new Configuration(['foo' => 1]);
        $value = null;
        $this->assertTrue(Configuration::tryGetInt('foo', $value));
        $this->assertEquals(1, $value);
    }

    public function testTryGetIntForNonExistentValueSetsItToNullAndReturnsFalse(): void
    {
        new Configuration(['foo' => ['bar' => 'baz']]);
        $value = null;
        $this->assertFalse(Configuration::tryGetInt('doesNotExist', $value));
        $this->assertNull($value);
    }

    public function testTryGetStringForExistentValueSetsItAndReturnsTrue(): void
    {
        new Configuration(['foo' => 'bar']);
        $value = null;
        $this->assertTrue(Configuration::tryGetString('foo', $value));
        $this->assertEquals('bar', $value);
    }

    public function testTryGetStringForNonExistentValueSetsItToNullAndReturnsFalse(): void
    {
        new Configuration(['foo' => ['bar' => 'baz']]);
        $value = null;
        $this->assertFalse(Configuration::tryGetString('doesNotExist', $value));
        $this->assertNull($value);
    }

    public function testTryGetValueForExistentValueSetsItAndReturnsTrue(): void
    {
        new Configuration(['foo' => 'bar']);
        $value = null;
        $this->assertTrue(Configuration::tryGetValue('foo', $value));
        $this->assertEquals('bar', $value);
    }

    public function testTryGetValueForNonExistentValueSetsItToNullAndReturnsFalse(): void
    {
        new Configuration(['foo' => ['bar' => 'baz']]);
        $value = null;
        $this->assertFalse(Configuration::tryGetValue('doesNotExist', $value));
        $this->assertNull($value);
    }
}
