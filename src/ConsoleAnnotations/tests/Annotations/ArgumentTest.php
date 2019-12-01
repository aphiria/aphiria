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

use Aphiria\Console\Input\ArgumentTypes;
use Aphiria\ConsoleAnnotations\Annotations\Argument;
use PHPUnit\Framework\TestCase;

/**
 * Tests the argument annotation
 */
class ArgumentTest extends TestCase
{
    public function testDefaultValuesOfArgumentPropertiesAreSet(): void
    {
        $argument = new Argument(['value' => 'foo', 'type' => ArgumentTypes::REQUIRED]);
        $this->assertEquals('foo', $argument->name);
        $this->assertEquals(ArgumentTypes::REQUIRED, $argument->type);
        $this->assertNull($argument->description);
        $this->assertNull($argument->defaultValue);
    }

    public function testNameCanBeSetViaName(): void
    {
        $argument = new Argument(['name' => 'foo', 'type' => ArgumentTypes::REQUIRED]);
        $this->assertEquals('foo', $argument->name);
    }

    public function testNameCanBeSetViaValue(): void
    {
        $argument = new Argument(['value' => 'foo', 'type' => ArgumentTypes::REQUIRED]);
        $this->assertEquals('foo', $argument->name);
    }

    public function testPropertiesAreSetViaConstructor(): void
    {
        $argument = new Argument([
            'value' => 'foo',
            'type' => ArgumentTypes::REQUIRED,
            'description' => 'description',
            'defaultValue' => 'val'
        ]);
        $this->assertEquals('foo', $argument->name);
        $this->assertEquals(ArgumentTypes::REQUIRED, $argument->type);
        $this->assertEquals('description', $argument->description);
        $this->assertEquals('val', $argument->defaultValue);
    }
}
