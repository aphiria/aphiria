<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output\Formatters;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Input\Argument;
use Aphiria\Console\Input\ArgumentType;
use Aphiria\Console\Input\Option;
use Aphiria\Console\Input\OptionType;
use Aphiria\Console\Output\Formatters\CommandFormatter;
use PHPUnit\Framework\TestCase;

class CommandFormatterTest extends TestCase
{
    private CommandFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new CommandFormatter();
    }

    public function testFormattingCommandWithMixOfArguments(): void
    {
        $command = new Command(
            'foo',
            [
                new Argument('bar', ArgumentType::Required, 'Bar argument'),
                new Argument('baz', ArgumentType::Optional, 'Baz argument'),
                new Argument('blah', ArgumentType::IsArray, 'Blah argument')
            ],
            [],
            ''
        );
        $this->assertSame('foo bar [baz] blah1...blahN', $this->formatter->format($command));
    }

    public function testFormattingCommandWithMultipleArguments(): void
    {
        $command = new Command(
            'foo',
            [
                new Argument('bar', ArgumentType::Required, 'Bar argument'),
                new Argument('baz', ArgumentType::Required, 'Baz argument')
            ],
            [],
            ''
        );
        $this->assertSame('foo bar baz', $this->formatter->format($command));
    }

    public function testFormattingCommandWithNoArgumentsOrOptions(): void
    {
        $command = new Command(
            'foo',
            [],
            [],
            'Foo command'
        );
        $this->assertSame('foo', $this->formatter->format($command));
    }

    public function testFormattingCommandWithOneArgument(): void
    {
        $command = new Command(
            'foo',
            [
                new Argument('bar', ArgumentType::Required, 'Bar argument')
            ],
            [],
            'Foo command'
        );
        $this->assertSame('foo bar', $this->formatter->format($command));
    }

    public function testFormattingCommandWithOneOptionWithDefaultValue(): void
    {
        $command = new Command(
            'foo',
            [],
            [
                new Option('bar', OptionType::OptionalValue, 'b', 'Bar option', 'yes')
            ],
            'Foo command'
        );
        $this->assertSame('foo [--bar=yes|-b]', $this->formatter->format($command));
    }

    public function testFormattingCommandWithOneOptionWithDefaultValueButNoShortName(): void
    {
        $command = new Command(
            'foo',
            [],
            [
                new Option('bar', OptionType::OptionalValue, null, 'Bar option', 'yes')
            ],
            'Foo command'
        );
        $this->assertSame('foo [--bar=yes]', $this->formatter->format($command));
    }

    public function testFormattingCommandWithOneOptionWithoutShortName(): void
    {
        $command = new Command(
            'foo',
            [],
            [
                new Option('bar', OptionType::NoValue, null, 'Bar option')
            ],
            'Foo command'
        );
        $this->assertSame('foo [--bar]', $this->formatter->format($command));
    }

    public function testFormattingCommandWithOneOptionalArgument(): void
    {
        $command = new Command(
            'foo',
            [
                new Argument('bar', ArgumentType::Optional, 'Bar argument')
            ],
            [],
            'Foo command'
        );
        $this->assertSame('foo [bar]', $this->formatter->format($command));
    }

    public function testFormattingCommandWithOptionalArrayArgument(): void
    {
        $command = new Command(
            'foo',
            [
                new Argument('blah', [ArgumentType::IsArray, ArgumentType::Optional], 'Blah argument')
            ],
            [],
            'Foo command'
        );
        $this->assertSame('foo [blah1]...[blahN]', $this->formatter->format($command));
    }
}
