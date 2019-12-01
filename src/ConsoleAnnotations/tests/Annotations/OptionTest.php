<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ConsoleAnnotations\Tests\Annotations;

use Aphiria\Console\Input\OptionTypes;
use Aphiria\ConsoleAnnotations\Annotations\Option;
use PHPUnit\Framework\TestCase;

/**
 * Tests the option annotation
 */
class OptionTest extends TestCase
{
    public function testDefaultValuesOfOptionPropertiesAreSet(): void
    {
        $option = new Option(['value' => 'foo', 'type' => OptionTypes::REQUIRED_VALUE]);
        $this->assertEquals('foo', $option->name);
        $this->assertNull($option->shortName);
        $this->assertEquals(OptionTypes::REQUIRED_VALUE, $option->type);
        $this->assertNull($option->description);
        $this->assertNull($option->defaultValue);
    }

    public function testNameCanBeSetViaName(): void
    {
        $option = new Option(['name' => 'foo', 'type' => OptionTypes::REQUIRED_VALUE]);
        $this->assertEquals('foo', $option->name);
    }

    public function testNameCanBeSetViaValue(): void
    {
        $option = new Option(['value' => 'foo', 'type' => OptionTypes::REQUIRED_VALUE]);
        $this->assertEquals('foo', $option->name);
    }

    public function testPropertiesAreSetViaConstructor(): void
    {
        $option = new Option([
            'value' => 'foo',
            'shortName' => 'f',
            'type' => OptionTypes::REQUIRED_VALUE,
            'description' => 'description',
            'defaultValue' => 'val'
        ]);
        $this->assertEquals('foo', $option->name);
        $this->assertEquals('f', $option->shortName);
        $this->assertEquals(OptionTypes::REQUIRED_VALUE, $option->type);
        $this->assertEquals('description', $option->description);
        $this->assertEquals('val', $option->defaultValue);
    }
}
