<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Input\Compilers;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Input\Argument;
use Aphiria\Console\Input\ArgumentTypes;
use Aphiria\Console\Input\Compilers\InputCompiler;
use Aphiria\Console\Input\Compilers\Tokenizers\IInputTokenizer;
use Aphiria\Console\Input\Option;
use Aphiria\Console\Input\OptionTypes;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Tests the input compiler
 */
class InputCompilerTest extends TestCase
{
    /** @var InputCompiler */
    private $compiler;
    /** @var CommandRegistry */
    private $commands;
    /** @var IInputTokenizer|MockObject */
    private $tokenizer;

    protected function setUp(): void
    {
        $this->commands = new CommandRegistry();
        $this->tokenizer = $this->createMock(IInputTokenizer::class);
        $this->compiler = new InputCompiler($this->commands, $this->tokenizer);
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
            $this->createMock(ICommandHandler::class)
        );
        $this->tokenizer->method('tokenize')
            ->with('foo bar\\baz')
            ->willReturn(['foo', 'bar\\baz']);
        $input = $this->compiler->compile('foo bar\\baz');
        $this->assertEquals('bar\\baz', $input->arguments['arg']);
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
            $this->createMock(ICommandHandler::class)
        );
        $this->tokenizer->method('tokenize')
            ->with('foo bar -r --opt1=dave')
            ->willReturn(['foo', 'bar', '-r', '--opt1=dave']);
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
            $this->createMock(ICommandHandler::class)
        );
        $this->tokenizer->method('tokenize')
            ->with('foo bar baz')
            ->willReturn(['foo', 'bar', 'baz']);
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
            $this->createMock(ICommandHandler::class)
        );
        $this->tokenizer->method('tokenize')
            ->with('foo bar baz')
            ->willReturn(['foo', 'bar', 'baz']);
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
            $this->createMock(ICommandHandler::class)
        );
        $this->tokenizer->method('tokenize')
            ->with('foo bar baz')
            ->willReturn(['foo', 'bar', 'baz']);
        $this->compiler->compile('foo bar baz');
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
            $this->createMock(ICommandHandler::class)
        );
        $this->tokenizer->method('tokenize')
            ->with('foo --opt=dave --opt=young')
            ->willReturn(['foo', '--opt=dave', '--opt=young']);
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
            $this->createMock(ICommandHandler::class)
        );
        $this->tokenizer->method('tokenize')
            ->with('foo --opt dave --opt young')
            ->willReturn(['foo', '--opt', 'dave', '--opt', 'young']);
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
            $this->createMock(ICommandHandler::class)
        );
        $this->tokenizer->method('tokenize')
            ->with('foo')
            ->willReturn(['foo']);
        $input = $this->compiler->compile('foo');
        $this->assertEquals('foo', $input->commandName);
        $this->assertEquals([], $input->arguments);
        $this->assertEquals([], $input->options);
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
            $this->createMock(ICommandHandler::class)
        );
        $this->tokenizer->method('tokenize')
            ->with('foo --opt=dave')
            ->willReturn(['foo', '--opt=dave']);
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
            $this->createMock(ICommandHandler::class)
        );
        $this->tokenizer->method('tokenize')
            ->with('foo --opt dave')
            ->willReturn(['foo', '--opt', 'dave']);
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
            $this->createMock(ICommandHandler::class)
        );
        $this->tokenizer->method('tokenize')
            ->with('foo --opt dave bar')
            ->willReturn(['foo', '--opt', 'dave', 'bar']);
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
            $this->createMock(ICommandHandler::class)
        );
        $this->tokenizer->method('tokenize')
            ->with("foo --opt1 'dave' --opt2=\"young\"")
            ->willReturn(['foo', '--opt1', 'dave', '--opt2="young"']);
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
            $this->createMock(ICommandHandler::class)
        );
        $this->tokenizer->method('tokenize')
            ->with('foo bar baz blah')
            ->willReturn(['foo', 'bar', 'baz', 'blah']);
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
            $this->createMock(ICommandHandler::class)
        );
        $this->tokenizer->method('tokenize')
            ->with('foo -r -f -d')
            ->willReturn(['foo', '-r', '-f', '-d']);
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
            $this->createMock(ICommandHandler::class)
        );
        $this->tokenizer->method('tokenize')
            ->with('foo -rfd')
            ->willReturn(['foo', '-rfd']);
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
            $this->createMock(ICommandHandler::class)
        );
        $this->tokenizer->method('tokenize')
            ->with('foo --opt=bar')
            ->willReturn(['foo', '--opt=bar']);
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
            $this->createMock(ICommandHandler::class)
        );
        $this->tokenizer->method('tokenize')
            ->with('foo')
            ->willReturn(['foo']);
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
            $this->createMock(ICommandHandler::class)
        );
        $this->tokenizer->method('tokenize')
            ->with('foo bar')
            ->willReturn(['foo', 'bar']);
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
            $this->createMock(ICommandHandler::class)
        );
        $this->tokenizer->method('tokenize')
            ->with('foo --opt')
            ->willReturn(['foo', '--opt']);
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
            $this->createMock(ICommandHandler::class)
        );
        $this->tokenizer->method('tokenize')
            ->with('foo bar')
            ->willReturn(['foo', 'bar']);
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
            $this->createMock(ICommandHandler::class)
        );
        $this->tokenizer->method('tokenize')
            ->with('foo -r')
            ->willReturn(['foo', '-r']);
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
            $this->createMock(ICommandHandler::class)
        );
        $this->tokenizer->method('tokenize')
            ->with('foo --opt1 --opt2')
            ->willReturn(['foo', '--opt1', '--opt2']);
        $input = $this->compiler->compile('foo --opt1 --opt2');
        $this->assertEquals('foo', $input->commandName);
        $this->assertEquals([], $input->arguments);
        $this->assertEquals(null, $input->options['opt1']);
        $this->assertEquals(null, $input->options['opt2']);
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
            $this->createMock(ICommandHandler::class)
        );
        $this->tokenizer->method('tokenize')
            ->with('foo')
            ->willReturn(['foo']);
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
            $this->createMock(ICommandHandler::class)
        );
        $this->tokenizer->method('tokenize')
            ->with('foo bar baz')
            ->willReturn(['foo', 'bar', 'baz']);
        $this->compiler->compile('foo bar baz');
    }
}
