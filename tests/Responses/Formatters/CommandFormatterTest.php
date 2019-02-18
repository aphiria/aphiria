<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Responses\Formatters;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Requests\Argument;
use Aphiria\Console\Requests\ArgumentTypes;
use Aphiria\Console\Requests\Option;
use Aphiria\Console\Requests\OptionTypes;
use Aphiria\Console\Responses\Formatters\CommandFormatter;
use PHPUnit\Framework\TestCase;

/**
 * Tests the command formatter
 */
class CommandFormatterTest extends TestCase
{
    /** @var CommandFormatter */
    private $formatter;

    public function setUp(): void
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
        $this->assertEquals('foo bar [baz] blah1...blahN', $this->formatter->format($command));
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
        $this->assertEquals('foo bar baz', $this->formatter->format($command));
    }

    public function testFormattingCommandWithNoArgumentsOrOptions(): void
    {
        $command = new Command(
            'foo',
            [],
            [],
            'Foo command'
        );
        $this->assertEquals('foo', $this->formatter->format($command));
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
        $this->assertEquals('foo bar', $this->formatter->format($command));
    }

    public function testFormattingCommandWithOneOptionWithDefaultValue(): void
    {
        $command = new Command(
            'foo',
            [],
            [
                new Option('bar', 'b', OptionTypes::OPTIONAL_VALUE, 'Bar option', 'yes')
            ],
            'Foo command'
        );
        $this->assertEquals('foo [--bar=yes|-b]', $this->formatter->format($command));
    }

    public function testFormattingCommandWithOneOptionWithDefaultValueButNoShortName(): void
    {
        $command = new Command(
            'foo',
            [],
            [
                new Option('bar', null, OptionTypes::OPTIONAL_VALUE, 'Bar option', 'yes')
            ],
            'Foo command'
        );
        $this->assertEquals('foo [--bar=yes]', $this->formatter->format($command));
    }

    public function testFormattingCommandWithOneOptionWithoutShortName(): void
    {
        $command = new Command(
            'foo',
            [],
            [
                new Option('bar', null, OptionTypes::NO_VALUE, 'Bar option')
            ],
            'Foo command'
        );
        $this->assertEquals('foo [--bar]', $this->formatter->format($command));
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
        $this->assertEquals('foo [bar]', $this->formatter->format($command));
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
        $this->assertEquals('foo [blah1]...[blahN]', $this->formatter->format($command));
    }
}
