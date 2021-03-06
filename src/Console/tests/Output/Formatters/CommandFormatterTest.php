<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output\Formatters;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Input\Argument;
use Aphiria\Console\Input\ArgumentTypes;
use Aphiria\Console\Input\Option;
use Aphiria\Console\Input\OptionTypes;
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
                new Argument('bar', ArgumentTypes::REQUIRED, 'Bar argument'),
                new Argument('baz', ArgumentTypes::OPTIONAL, 'Baz argument'),
                new Argument('blah', ArgumentTypes::IS_ARRAY, 'Blah argument')
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
                new Argument('bar', ArgumentTypes::REQUIRED, 'Bar argument'),
                new Argument('baz', ArgumentTypes::REQUIRED, 'Baz argument')
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
                new Argument('bar', ArgumentTypes::REQUIRED, 'Bar argument')
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
                new Option('bar', OptionTypes::OPTIONAL_VALUE, 'b', 'Bar option', 'yes')
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
                new Option('bar', OptionTypes::OPTIONAL_VALUE, null, 'Bar option', 'yes')
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
                new Option('bar', OptionTypes::NO_VALUE, null, 'Bar option')
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
                new Argument('bar', ArgumentTypes::OPTIONAL, 'Bar argument')
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
                new Argument('blah', ArgumentTypes::IS_ARRAY | ArgumentTypes::OPTIONAL, 'Blah argument')
            ],
            [],
            'Foo command'
        );
        $this->assertSame('foo [blah1]...[blahN]', $this->formatter->format($command));
    }
}
