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
use Aphiria\Console\Requests\Argument;
use Aphiria\Console\Requests\ArgumentTypes;
use Aphiria\Console\Requests\Option;
use Aphiria\Console\Requests\OptionTypes;
use Aphiria\Console\Requests\Request;
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
        $request = new Request('name', ['bar', 'baz'], []);
        $commandInput = $this->factory->createCommandInput($command, $request);
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
        $request = new Request('name', ['bar', 'baz'], []);
        $commandInput = $this->factory->createCommandInput($command, $request);
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
        $request = new Request('name', ['bar', 'baz'], []);
        $this->factory->createCommandInput($command, $request);
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
        $request = new Request('name', [], []);
        $commandInput = $this->factory->createCommandInput($command, $request);
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
        $request = new Request('name', [], ['foo' => 'bar']);
        $this->factory->createCommandInput($command, $request);
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
        $request = new Request('name', [], ['foo' => 'bar']);
        $commandInput = $this->factory->createCommandInput($command, $request);
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
        $request = new Request('name', ['bar'], []);
        $commandInput = $this->factory->createCommandInput($command, $request);
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
        $request = new Request('name', [], []);
        $commandInput = $this->factory->createCommandInput($command, $request);
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
        $request = new Request('name', [], []);
        $commandInput = $this->factory->createCommandInput($command, $request);
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
        $request = new Request('name', [], ['foo' => null]);
        $commandInput = $this->factory->createCommandInput($command, $request);
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
        $request = new Request('name', ['bar'], []);
        $commandInput = $this->factory->createCommandInput($command, $request);
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
        $request = new Request('name', ['bar'], []);
        $commandInput = $this->factory->createCommandInput($command, $request);
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
        $request = new Request('name', [], []);
        $this->factory->createCommandInput($command, $request);
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
        $request = new Request('name', ['bar'], []);
        $this->factory->createCommandInput($command, $request);
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
        $request = new Request('name', [], ['foo' => 'bar']);
        $commandInput = $this->factory->createCommandInput($command, $request);
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
        $request = new Request('name', [], ['foo' => null]);
        $this->factory->createCommandInput($command, $request);
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
        $request = new Request('name', [], []);
        $commandInput = $this->factory->createCommandInput($command, $request);
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
        $request = new Request('name', ['bar', 'baz'], []);
        $this->factory->createCommandInput($command, $request);
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
        $request = new Request('name', [], ['f' => null]);
        $commandInput = $this->factory->createCommandInput($command, $request);
        $this->assertEquals('bar', $commandInput->options['foo']);
    }
}
