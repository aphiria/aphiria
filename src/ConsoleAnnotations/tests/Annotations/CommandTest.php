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
use Aphiria\Console\Input\OptionTypes;
use Aphiria\ConsoleAnnotations\Annotations\Argument;
use Aphiria\ConsoleAnnotations\Annotations\Command;
use Aphiria\ConsoleAnnotations\Annotations\Option;
use PHPUnit\Framework\TestCase;

/**
 * Tests the command annotation
 */
class CommandTest extends TestCase
{
    public function testDefaultValuesOfCommandPropertiesAreSet(): void
    {
        $command = new Command(['value' => 'foo']);
        $this->assertEquals('foo', $command->name);
        $this->assertEmpty($command->arguments);
        $this->assertEmpty($command->options);
        $this->assertNull($command->description);
        $this->assertNull($command->helpText);
    }

    public function testNameCanBeSetViaName(): void
    {
        $command = new Command(['name' => 'foo']);
        $this->assertEquals('foo', $command->name);
    }

    public function testNameCanBeSetViaValue(): void
    {
        $command = new Command(['value' => 'foo']);
        $this->assertEquals('foo', $command->name);
    }

    public function testPropertiesAreSetViaConstructor(): void
    {
        $command = new Command([
            'value' => 'foo',
            'arguments' => [new Argument(['value' => 'arg1', 'type' => ArgumentTypes::REQUIRED])],
            'options' => [new Option(['value' => 'opt1', 'type' => OptionTypes::REQUIRED_VALUE])],
            'description' => 'command description',
            'helpText' => 'help text'
        ]);
        $this->assertEquals('foo', $command->name);
        $this->assertCount(1, $command->arguments);
        $this->assertEquals('arg1', $command->arguments[0]->name);
        $this->assertEquals(ArgumentTypes::REQUIRED, $command->arguments[0]->type);
        $this->assertCount(1, $command->options);
        $this->assertEquals('opt1', $command->options[0]->name);
        $this->assertEquals(OptionTypes::REQUIRED_VALUE, $command->options[0]->type);
        $this->assertEquals('command description', $command->description);
        $this->assertEquals('help text', $command->helpText);
    }
}
