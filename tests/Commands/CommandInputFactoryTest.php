<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Commands;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandInputFactory;
use Aphiria\Console\Input\Argument;
use Aphiria\Console\Input\ArgumentTypes;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Input\Option;
use Aphiria\Console\Input\OptionTypes;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Tests the command input factory
 */
class CommandInputFactoryTest extends TestCase
{
    /** @var CommandInputFactory */
    private $factory;

    public function setUp(): void
    {
        $this->factory = new CommandInputFactory();
    }

    public function testCompilingArrayArgument(): void
    {
        $command = new Command('name', [new Argument('foo', ArgumentTypes::IS_ARRAY, 'descr')], [], '', '');
        $input = new Input('name', ['bar', 'baz'], []);
        $commandInput = $this->factory->createCommandInput($command, $input);
        $this->assertEquals(['bar', 'baz'], $commandInput->arguments['foo']);
    }

    public function testCompilingArrayArgumentWitOptionalArgumentAfter(): void
    {
        $command = new Command(
            'name',
            [
                new Argument('foo', ArgumentTypes::IS_ARRAY, 'descr'),
                new Argument('bar', ArgumentTypes::OPTIONAL, 'descr', 'baz')
            ],
            [],
            '',
            ''
        );
        $input = new Input('name', ['bar', 'baz'], []);
        $commandInput = $this->factory->createCommandInput($command, $input);
        $this->assertEquals(['bar', 'baz'], $commandInput->arguments['foo']);
        $this->assertEquals('baz', $commandInput->arguments['bar']);
    }

    public function testCompilingArrayArgumentWitRequiredArgumentAfter(): void
    {
        $this->expectException(RuntimeException::class);
        $command = new Command(
            'name',
            [
                new Argument('foo', ArgumentTypes::IS_ARRAY, 'descr'),
                new Argument('bar', ArgumentTypes::REQUIRED, 'descr')
            ],
            [],
            '',
            ''
        );
        $input = new Input('name', ['bar', 'baz'], []);
        $this->factory->createCommandInput($command, $input);
    }

    public function testCompilingNoValueOption(): void
    {
        $command = new Command(
            'name',
            [],
            [new Option('foo', 'f', OptionTypes::NO_VALUE, 'descr')],
            '',
            ''
        );
        $input = new Input('name', [], []);
        $commandInput = $this->factory->createCommandInput($command, $input);
        $this->assertFalse(isset($commandInput->options['foo']));
    }

    public function testCompilingNoValueOptionWithAValue(): void
    {
        $this->expectException(RuntimeException::class);
        $command = new Command(
            'name',
            [],
            [new Option('foo', 'f', OptionTypes::NO_VALUE, 'descr')],
            '',
            ''
        );
        $input = new Input('name', [], ['foo' => 'bar']);
        $this->factory->createCommandInput($command, $input);
    }

    public function testCompilingOptionWithNullShortNameStillCompiles(): void
    {
        $command = new Command(
            'name',
            [],
            [new Option('foo', null, OptionTypes::REQUIRED_VALUE, 'descr')],
            '',
            ''
        );
        $input = new Input('name', [], ['foo' => 'bar']);
        $commandInput = $this->factory->createCommandInput($command, $input);
        $this->assertEquals('bar', $commandInput->options['foo']);
    }

    public function testCompilingOptionalArgument(): void
    {
        $command = new Command(
            'name',
            [new Argument('foo', ArgumentTypes::OPTIONAL, 'descr')],
            [],
            '',
            ''
        );
        $input = new Input('name', ['bar'], []);
        $commandInput = $this->factory->createCommandInput($command, $input);
        $this->assertEquals('bar', $commandInput->arguments['foo']);
    }

    public function testCompilingOptionalArgumentWithDefaultValue(): void
    {
        $command = new Command(
            'name',
            [new Argument('foo', ArgumentTypes::OPTIONAL, 'descr', 'baz')],
            [],
            '',
            ''
        );
        $input = new Input('name', [], []);
        $commandInput = $this->factory->createCommandInput($command, $input);
        $this->assertEquals('baz', $commandInput->arguments['foo']);
    }

    public function testCompilingOptionalArgumentsWithoutAnyValues(): void
    {
        $command = new Command(
            'name',
            [
                new Argument('foo', ArgumentTypes::OPTIONAL, 'descr', 'fooValue'),
                new Argument('bar', ArgumentTypes::OPTIONAL, 'descr', 'barValue')
            ],
            [],
            '',
            ''
        );
        $input = new Input('name', [], []);
        $commandInput = $this->factory->createCommandInput($command, $input);
        $this->assertEquals('fooValue', $commandInput->arguments['foo']);
        $this->assertEquals('barValue', $commandInput->arguments['bar']);
    }

    public function testCompilingOptionalValueOptionWithDefaultValue(): void
    {
        $command = new Command(
            'name',
            [],
            [new Option('foo', 'f', OptionTypes::OPTIONAL_VALUE, 'descr', 'bar')],
            '',
            ''
        );
        $input = new Input('name', [], ['foo' => null]);
        $commandInput = $this->factory->createCommandInput($command, $input);
        $this->assertEquals('bar', $commandInput->options['foo']);
    }

    public function testCompilingRequiredAndOptionalArgument(): void
    {
        $command = new Command(
            'name',
            [
                new Argument('foo', ArgumentTypes::REQUIRED, 'descr'),
                new Argument('bar', ArgumentTypes::OPTIONAL, 'descr', 'baz')
            ],
            [],
            '',
            ''
        );
        $input = new Input('name', ['bar'], []);
        $commandInput = $this->factory->createCommandInput($command, $input);
        $this->assertEquals('bar', $commandInput->arguments['foo']);
        $this->assertEquals('baz', $commandInput->arguments['bar']);
    }

    public function testCompilingRequiredArgument(): void
    {
        $command = new Command(
            'name',
            [new Argument('foo', ArgumentTypes::REQUIRED, 'descr')],
            [],
            '',
            ''
        );
        $input = new Input('name', ['bar'], []);
        $commandInput = $this->factory->createCommandInput($command, $input);
        $this->assertEquals('bar', $commandInput->arguments['foo']);
    }

    public function testCompilingRequiredArgumentWithoutValue(): void
    {
        $this->expectException(RuntimeException::class);
        $command = new Command(
            'name',
            [new Argument('foo', ArgumentTypes::REQUIRED, 'descr')],
            [],
            '',
            ''
        );
        $input = new Input('name', [], []);
        $this->factory->createCommandInput($command, $input);
    }

    public function testCompilingRequiredArgumentsWithoutSpecifyingAllValues(): void
    {
        $this->expectException(RuntimeException::class);
        $command = new Command(
            'name',
            [],
            [
                new Argument('foo', ArgumentTypes::REQUIRED, 'descr'),
                new Argument('bar', ArgumentTypes::REQUIRED, 'descr')
            ],
            '',
            ''
        );
        $input = new Input('name', ['bar'], []);
        $this->factory->createCommandInput($command, $input);
    }

    public function testCompilingRequiredValueOption(): void
    {
        $command = new Command(
            'name',
            [],
            [new Option('foo', 'f', OptionTypes::REQUIRED_VALUE, 'descr')],
            '',
            ''
        );
        $input = new Input('name', [], ['foo' => 'bar']);
        $commandInput = $this->factory->createCommandInput($command, $input);
        $this->assertEquals('bar', $commandInput->options['foo']);
    }

    public function testCompilingRequiredValueOptionWithoutValue(): void
    {
        $this->expectException(RuntimeException::class);
        $command = new Command(
            'name',
            [],
            [new Option('foo', 'f', OptionTypes::REQUIRED_VALUE, 'descr')],
            '',
            ''
        );
        $input = new Input('name', [], ['foo' => null]);
        $this->factory->createCommandInput($command, $input);
    }

    public function testDefaultValueIsUsedForOptionsThatAreNotSet(): void
    {
        $command = new Command(
            'name',
            [],
            [
                new Option('foo', 'f', OptionTypes::REQUIRED_VALUE, 'descr', 'foo value'),
                new Option('bar', 'b', OptionTypes::OPTIONAL_VALUE, 'descr', 'bar value'),
                new Option('baz', 'z', OptionTypes::NO_VALUE, 'Baz command', 'baz value')
            ],
            '',
            ''
        );
        $input = new Input('name', [], []);
        $commandInput = $this->factory->createCommandInput($command, $input);
        $this->assertEquals('foo value', $commandInput->options['foo']);
        $this->assertEquals('bar value', $commandInput->options['bar']);
        $this->assertFalse(isset($commandInput->options['baz']));
    }

    public function testPassingTooManyArguments(): void
    {
        $this->expectException(RuntimeException::class);
        $command = new Command(
            'name',
            [new Argument('foo', ArgumentTypes::REQUIRED, 'descr')],
            [],
            '',
            ''
        );
        $input = new Input('name', ['bar', 'baz'], []);
        $this->factory->createCommandInput($command, $input);
    }

    public function testThatShortAndLongOptionsPointToSameOption(): void
    {
        $command = new Command(
            'name',
            [],
            [new Option('foo', 'f', OptionTypes::OPTIONAL_VALUE, 'descr', 'bar')],
            '',
            ''
        );
        $input = new Input('name', [], ['f' => null]);
        $commandInput = $this->factory->createCommandInput($command, $input);
        $this->assertEquals('bar', $commandInput->options['foo']);
    }
}
