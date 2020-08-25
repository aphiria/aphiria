<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Commands\Annotations;

use Aphiria\Console\Commands\Annotations\Option;
use Aphiria\Console\Input\OptionTypes;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class OptionTest extends TestCase
{
    public function testDefaultValuesOfOptionPropertiesAreSet(): void
    {
        $option = new Option(['value' => 'foo', 'type' => OptionTypes::REQUIRED_VALUE]);
        $this->assertSame('foo', $option->name);
        $this->assertNull($option->shortName);
        $this->assertSame(OptionTypes::REQUIRED_VALUE, $option->type);
        $this->assertNull($option->description);
        $this->assertNull($option->defaultValue);
    }

    public function testNameCanBeSetViaName(): void
    {
        $option = new Option(['name' => 'foo', 'type' => OptionTypes::REQUIRED_VALUE]);
        $this->assertSame('foo', $option->name);
    }

    public function testNameCanBeSetViaValue(): void
    {
        $option = new Option(['value' => 'foo', 'type' => OptionTypes::REQUIRED_VALUE]);
        $this->assertSame('foo', $option->name);
    }

    public function testNoNameThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Option name must be set');
        new Option([]);
    }

    public function testNoTypeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Option type must be set');
        new Option(['value' => 'foo']);
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
        $this->assertSame('foo', $option->name);
        $this->assertSame('f', $option->shortName);
        $this->assertSame(OptionTypes::REQUIRED_VALUE, $option->type);
        $this->assertSame('description', $option->description);
        $this->assertSame('val', $option->defaultValue);
    }
}
