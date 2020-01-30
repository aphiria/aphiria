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

    public function testGetForNestedPathReturnsValue(): void
    {
        new Configuration(['foo' => ['bar' => ['baz' => 'blah']]]);
        $this->assertEquals('blah', Configuration::get('foo.bar.baz'));
    }

    public function testGetForNonExistentNestedPathThrowsException(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('No configuration value at foo.blah');
        new Configuration(['foo' => ['bar' => 'baz']]);
        Configuration::get('foo.blah');
    }

    public function testGetForNonExistentTopLevelPathThrowsException(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('No configuration value at baz');
        new Configuration(['foo' => 'bar']);
        Configuration::get('baz');
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetWithoutInstantiatingConfigurationThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Must call ' . Configuration::class . '::__construct() before calling get()');
        Configuration::get('foo');
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
}
