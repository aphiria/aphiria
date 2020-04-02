<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Input\Compilers;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Input\Argument;
use Aphiria\Console\Input\ArgumentTypes;
use Aphiria\Console\Input\Compilers\CommandNotFoundException;
use Aphiria\Console\Input\Compilers\InputCompiler;
use Aphiria\Console\Input\Option;
use Aphiria\Console\Input\OptionTypes;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Tests the input compiler
 */
class InputCompilerTest extends TestCase
{
    private InputCompiler $compiler;
    private CommandRegistry $commands;

    protected function setUp(): void
    {
        $this->commands = new CommandRegistry();
        $this->compiler = new InputCompiler($this->commands);
    }

    public function testBackslashesAreRespected(): void
    {
        $this->commands->registerCommand(
            new Command(
                'foo',
                [new Argument('arg', ArgumentTypes::REQUIRED, '')],
                [],
                ''
            ),
            fn () => $this->createMock(ICommandHandler::class)
        );
        $input = $this->compiler->compile('foo bar\\baz');
        $this->assertEquals('bar\\baz', $input->arguments['arg']);
    }

    public function testCompilingArgvInputIsCompiledCorrectly(): void
    {
        $this->commands->registerCommand(
            new Command('bar', [], [], ''),
            fn () => $this->createMock(ICommandHandler::class)
        );
        $input = $this->compiler->compile(['foo', 'bar']);
        $this->assertEquals('bar', $input->commandName);
    }

    public function testCompilingArgumentShortOptionLongOption(): void
    {
        $this->commands->registerCommand(
            new Command(
                'foo',
                [new Argument('arg', ArgumentTypes::REQUIRED, '')],
                [
                    new Option('opt1', null, OptionTypes::REQUIRED_VALUE, ''),
                    new Option('opt2', 'r', OptionTypes::NO_VALUE, '')
                ],
                ''
            ),
            fn () => $this->createMock(ICommandHandler::class)
        );
        $input = $this->compiler->compile('foo bar -r --opt1=dave');
        $this->assertEquals('foo', $input->commandName);
        $this->assertEquals('bar', $input->arguments['arg']);
        $this->assertNull($input->options['opt2']);
        $this->assertEquals('dave', $input->options['opt1']);
    }

    public function testCompilingArrayArgumentWithOptionalArgumentAfterIsAcceptable(): void
    {
        $this->commands->registerCommand(
            new Command(
                'foo',
                [
                    new Argument('arg1', ArgumentTypes::IS_ARRAY, ''),
                    new Argument('arg2', ArgumentTypes::OPTIONAL, '', 'blah')
                ],
                [],
                '',
                ''
            ),
            fn () => $this->createMock(ICommandHandler::class)
        );
        $input = $this->compiler->compile('foo bar baz');
        $this->assertEquals(['bar', 'baz'], $input->arguments['arg1']);
        $this->assertEquals('blah', $input->arguments['arg2']);
    }

    public function testCompilingArrayArgumentCreatesListOfValues(): void
    {
        $this->commands->registerCommand(
            new Command(
                'foo',
                [
                    new Argument('arg', ArgumentTypes::IS_ARRAY, '')
                ],
                [],
                '',
                '',
            ),
            fn () => $this->createMock(ICommandHandler::class)
        );
        $input = $this->compiler->compile('foo bar baz');
        $this->assertEquals(['bar', 'baz'], $input->arguments['arg']);
    }

    public function testCompilingArrayArgumentWithRequiredArgumentAfterThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->commands->registerCommand(
            new Command(
                'foo',
                [
                    new Argument('arg1', ArgumentTypes::IS_ARRAY, ''),
                    new Argument('arg2', ArgumentTypes::REQUIRED, '')
                ],
                [],
                '',
                '',
            ),
            fn () => $this->createMock(ICommandHandler::class)
        );
        $this->compiler->compile('foo bar baz');
    }

    public function testCompilingArrayListInputIsCompiledCorrectly(): void
    {
        $this->commands->registerCommand(
            new Command('foo', [], [], ''),
            fn () => $this->createMock(ICommandHandler::class)
        );
        $input = $this->compiler->compile(['name' => 'foo']);
        $this->assertEquals('foo', $input->commandName);
    }

    public function testCompilingArrayLongOptionWithEqualsSign(): void
    {
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [
                    new Option('opt', null, OptionTypes::IS_ARRAY, '')
                ],
                ''
            ),
            fn () => $this->createMock(ICommandHandler::class)
        );
        $input = $this->compiler->compile('foo --opt=dave --opt=young');
        $this->assertEquals('foo', $input->commandName);
        $this->assertEquals([], $input->arguments);
        $this->assertEquals(['dave', 'young'], $input->options['opt']);
    }

    public function testCompilingArrayLongOptionWithoutEqualsSign(): void
    {
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [
                    new Option('opt', null, OptionTypes::IS_ARRAY, '')
                ],
                ''
            ),
            fn () => $this->createMock(ICommandHandler::class)
        );
        $input = $this->compiler->compile('foo --opt dave --opt young');
        $this->assertEquals('foo', $input->commandName);
        $this->assertEquals([], $input->arguments);
        $this->assertEquals(['dave', 'young'], $input->options['opt']);
    }

    public function testCompilingCommandName(): void
    {
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [],
                ''
            ),
            fn () => $this->createMock(ICommandHandler::class)
        );
        $input = $this->compiler->compile('foo');
        $this->assertEquals('foo', $input->commandName);
        $this->assertEquals([], $input->arguments);
        $this->assertEquals([], $input->options);
    }

    public function testCompilingEmptyCommandNameThrowsException(): void
    {
        $this->expectException(CommandNotFoundException::class);
        $this->compiler->compile('');
    }

    public function testCompilingLongOptionWithEqualsSign(): void
    {
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [new Option('opt', null, OptionTypes::REQUIRED_VALUE, '')],
                ''
            ),
            fn () => $this->createMock(ICommandHandler::class)
        );
        $input = $this->compiler->compile('foo --opt=dave');
        $this->assertEquals('foo', $input->commandName);
        $this->assertEquals([], $input->arguments);
        $this->assertEquals('dave', $input->options['opt']);
    }

    public function testCompilingLongOptionWithoutEqualsSign(): void
    {
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [new Option('opt', null, OptionTypes::REQUIRED_VALUE, '')],
                ''
            ),
            fn () => $this->createMock(ICommandHandler::class)
        );
        $input = $this->compiler->compile('foo --opt dave');
        $this->assertEquals('foo', $input->commandName);
        $this->assertEquals([], $input->arguments);
        $this->assertEquals('dave', $input->options['opt']);
    }

    public function testCompilingLongOptionWithoutEqualsSignWithArgumentAfter(): void
    {
        $this->commands->registerCommand(
            new Command(
                'foo',
                [new Argument('arg', ArgumentTypes::REQUIRED, '')],
                [new Option('opt', null, OptionTypes::REQUIRED_VALUE, '')],
                ''
            ),
            fn () => $this->createMock(ICommandHandler::class)
        );
        $input = $this->compiler->compile('foo --opt dave bar');
        $this->assertEquals('foo', $input->commandName);
        $this->assertEquals('bar', $input->arguments['arg']);
        $this->assertEquals('dave', $input->options['opt']);
    }

    public function testCompilingLongOptionWithoutEqualsSignWithQuotedValue(): void
    {
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [
                    new Option('opt1', null, OptionTypes::REQUIRED_VALUE, ''),
                    new Option('opt2', null, OptionTypes::REQUIRED_VALUE, '')
                ],
                ''
            ),
            fn () => $this->createMock(ICommandHandler::class)
        );
        $input = $this->compiler->compile("foo --opt1 'dave' --opt2=\"young\"");
        $this->assertEquals('foo', $input->commandName);
        $this->assertEquals([], $input->arguments);
        $this->assertEquals('dave', $input->options['opt1']);
        $this->assertEquals('young', $input->options['opt2']);
    }


    public function testCompilingMultipleArgument(): void
    {
        $this->commands->registerCommand(
            new Command(
                'foo',
                [
                    new Argument('arg1', ArgumentTypes::OPTIONAL, ''),
                    new Argument('arg2', ArgumentTypes::OPTIONAL, ''),
                    new Argument('arg3', ArgumentTypes::OPTIONAL, '')
                ],
                [],
                ''
            ),
            fn () => $this->createMock(ICommandHandler::class)
        );
        $input = $this->compiler->compile('foo bar baz blah');
        $this->assertEquals('foo', $input->commandName);
        $this->assertEquals('bar', $input->arguments['arg1']);
        $this->assertEquals('baz', $input->arguments['arg2']);
        $this->assertEquals('blah', $input->arguments['arg3']);
        $this->assertEquals([], $input->options);
    }

    public function testCompilingMultipleSeparateShortOptions(): void
    {
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [
                    new Option('opt1', 'r', OptionTypes::NO_VALUE, ''),
                    new Option('opt2', 'f', OptionTypes::NO_VALUE, ''),
                    new Option('opt3', 'd', OptionTypes::NO_VALUE, '')
                ],
                ''
            ),
            fn () => $this->createMock(ICommandHandler::class)
        );
        $input = $this->compiler->compile('foo -r -f -d');
        $this->assertEquals('foo', $input->commandName);
        $this->assertNull($input->options['opt1']);
        $this->assertNull($input->options['opt2']);
        $this->assertNull($input->options['opt3']);
        $this->assertEquals([], $input->arguments);
    }

    public function testCompilingMultipleShortOptions(): void
    {
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [
                    new Option('opt1', 'r', OptionTypes::NO_VALUE, ''),
                    new Option('opt2', 'f', OptionTypes::NO_VALUE, ''),
                    new Option('opt3', 'd', OptionTypes::NO_VALUE, '')
                ],
                ''
            ),
            fn () => $this->createMock(ICommandHandler::class)
        );
        $input = $this->compiler->compile('foo -rfd');
        $this->assertEquals('foo', $input->commandName);
        $this->assertNull($input->options['opt1']);
        $this->assertNull($input->options['opt2']);
        $this->assertNull($input->options['opt3']);
        $this->assertEquals([], $input->arguments);
    }

    public function testCompilingNoValueOptionWithValueThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [new Option('opt', null, OptionTypes::NO_VALUE, '')],
                '',
                ''
            ),
            fn () => $this->createMock(ICommandHandler::class)
        );
        $this->compiler->compile('foo --opt=bar');
    }

    public function testCompilingOptionalArgumentWithDefaultValueUsesDefaultValueWhenNoValueIsPassedIn(): void
    {
        $this->commands->registerCommand(
            new Command(
                'foo',
                [new Argument('arg', ArgumentTypes::OPTIONAL, '', 'bar')],
                [],
                '',
                ''
            ),
            fn () => $this->createMock(ICommandHandler::class)
        );
        $input = $this->compiler->compile('foo');
        $this->assertEquals('bar', $input->arguments['arg']);
    }

    public function testCompilingRequiredArgumentsWithoutSpecifyingAllValuesThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [
                    new Argument('arg1', ArgumentTypes::REQUIRED, ''),
                    new Argument('arg2', ArgumentTypes::REQUIRED, '')
                ],
                '',
                ''
            ),
            fn () => $this->createMock(ICommandHandler::class)
        );
        $this->compiler->compile('foo bar');
    }

    public function testCompilingRequiredValueOptionWithoutValueThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [new Option('opt', null, OptionTypes::REQUIRED_VALUE, '')],
                '',
                ''
            ),
            fn () => $this->createMock(ICommandHandler::class)
        );
        $this->compiler->compile('foo --opt');
    }

    public function testCompilingSingleArgument(): void
    {
        $this->commands->registerCommand(
            new Command(
                'foo',
                [new Argument('arg', ArgumentTypes::REQUIRED, '')],
                [],
                ''
            ),
            fn () => $this->createMock(ICommandHandler::class)
        );
        $input = $this->compiler->compile('foo bar');
        $this->assertEquals('foo', $input->commandName);
        $this->assertEquals('bar', $input->arguments['arg']);
        $this->assertEquals([], $input->options);
    }

    public function testCompilingSingleShortOption(): void
    {
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [new Option('opt', 'r', OptionTypes::NO_VALUE, '')],
                ''
            ),
            fn () => $this->createMock(ICommandHandler::class)
        );
        $input = $this->compiler->compile('foo -r');
        $this->assertEquals('foo', $input->commandName);
        $this->assertNull($input->options['opt']);
        $this->assertEquals([], $input->arguments);
    }

    public function testCompilingTwoConsecutiveLongOptions(): void
    {
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [
                    new Option('opt1', null, OptionTypes::NO_VALUE, ''),
                    new Option('opt2', null, OptionTypes::NO_VALUE, '')
                ],
                ''
            ),
            fn () => $this->createMock(ICommandHandler::class)
        );
        $input = $this->compiler->compile('foo --opt1 --opt2');
        $this->assertEquals('foo', $input->commandName);
        $this->assertEquals([], $input->arguments);
        $this->assertEquals(null, $input->options['opt1']);
        $this->assertEquals(null, $input->options['opt2']);
    }

    public function testCompilingStringInputIsCompiledCorrectly(): void
    {
        $this->commands->registerCommand(
            new Command('foo', [], [], ''),
            fn () => $this->createMock(ICommandHandler::class)
        );
        $input = $this->compiler->compile('foo');
        $this->assertEquals('foo', $input->commandName);
    }

    public function testCompilingUnregisteredCommandThrowsException(): void
    {
        $this->expectException(CommandNotFoundException::class);
        $this->compiler->compile('foo');
    }

    public function testCompilingUsesDefaultValuesForOptionsThatAreNotSet(): void
    {
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [
                    new Option('foo', 'f', OptionTypes::REQUIRED_VALUE, '', 'foo value'),
                    new Option('bar', 'b', OptionTypes::OPTIONAL_VALUE, '', 'bar value'),
                    new Option('baz', 'z', OptionTypes::NO_VALUE, 'Baz command', 'baz value')
                ],
                '',
                ''
            ),
            fn () => $this->createMock(ICommandHandler::class)
        );
        $input = $this->compiler->compile('foo');
        $this->assertEquals('foo value', $input->options['foo']);
        $this->assertEquals('bar value', $input->options['bar']);
        $this->assertFalse(isset($input->options['baz']));
    }

    public function testCompilingWithTooManyArgumentsThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->commands->registerCommand(
            new Command(
                'foo',
                [new Argument('arg', ArgumentTypes::REQUIRED, '')],
                [],
                ''
            ),
            fn () => $this->createMock(ICommandHandler::class)
        );
        $this->compiler->compile('foo bar baz');
    }
}
