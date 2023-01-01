<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Input\Compilers;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Input\Argument;
use Aphiria\Console\Input\ArgumentType;
use Aphiria\Console\Input\Compilers\CommandNotFoundException;
use Aphiria\Console\Input\Compilers\TokenizerInputCompiler;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Input\Option;
use Aphiria\Console\Input\OptionType;
use Aphiria\Console\Output\IOutput;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class TokenizerInputCompilerTest extends TestCase
{
    private TokenizerInputCompiler $compiler;
    private CommandRegistry $commands;

    protected function setUp(): void
    {
        $this->commands = new CommandRegistry();
        $this->compiler = new TokenizerInputCompiler($this->commands);
    }

    public function testBackslashesAreRespected(): void
    {
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(
            new Command(
                'foo',
                [new Argument('arg', ArgumentType::Required, '')],
                [],
                ''
            ),
            $commandHandler::class
        );
        $input = $this->compiler->compile('foo bar\\baz');
        $this->assertSame('bar\\baz', $input->arguments['arg']);
    }

    public function testCompilingArgvInputIsCompiledCorrectly(): void
    {
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(
            new Command('bar', [], [], ''),
            $commandHandler::class
        );
        $input = $this->compiler->compile(['foo', 'bar']);
        $this->assertSame('bar', $input->commandName);
    }

    public function testCompilingArgumentShortOptionLongOption(): void
    {
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(
            new Command(
                'foo',
                [new Argument('arg', ArgumentType::Required, '')],
                [
                    new Option('opt1', OptionType::RequiredValue, null, ''),
                    new Option('opt2', OptionType::NoValue, 'r', '')
                ],
                ''
            ),
            $commandHandler::class
        );
        $input = $this->compiler->compile('foo bar -r --opt1=dave');
        $this->assertSame('foo', $input->commandName);
        $this->assertSame('bar', $input->arguments['arg']);
        $this->assertNull($input->options['opt2']);
        $this->assertSame('dave', $input->options['opt1']);
    }

    public function testCompilingArrayArgumentWithOptionalArgumentAfterIsAcceptable(): void
    {
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(
            new Command(
                'foo',
                [
                    new Argument('arg1', ArgumentType::IsArray, ''),
                    new Argument('arg2', ArgumentType::Optional, '', 'blah')
                ],
                [],
                '',
                ''
            ),
            $commandHandler::class
        );
        $input = $this->compiler->compile('foo bar baz');
        $this->assertEquals(['bar', 'baz'], $input->arguments['arg1']);
        $this->assertSame('blah', $input->arguments['arg2']);
    }

    public function testCompilingArrayArgumentCreatesListOfValues(): void
    {
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(
            new Command(
                'foo',
                [
                    new Argument('arg', ArgumentType::IsArray, '')
                ],
                [],
                '',
                '',
            ),
            $commandHandler::class
        );
        $input = $this->compiler->compile('foo bar baz');
        $this->assertEquals(['bar', 'baz'], $input->arguments['arg']);
    }

    public function testCompilingArrayArgumentWithRequiredArgumentAfterThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(
            new Command(
                'foo',
                [
                    new Argument('arg1', ArgumentType::IsArray, ''),
                    new Argument('arg2', ArgumentType::Required, '')
                ],
                [],
                '',
                '',
            ),
            $commandHandler::class
        );
        $this->compiler->compile('foo bar baz');
    }

    public function testCompilingArrayListInputIsCompiledCorrectly(): void
    {
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(new Command('foo', [], [], ''), $commandHandler::class);
        $input = $this->compiler->compile(['name' => 'foo']);
        $this->assertSame('foo', $input->commandName);
    }

    public function testCompilingArrayLongOptionWithEqualsSign(): void
    {
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [
                    new Option('opt', OptionType::IsArray, null, '')
                ],
                ''
            ),
            $commandHandler::class
        );
        $input = $this->compiler->compile('foo --opt=dave --opt=young');
        $this->assertSame('foo', $input->commandName);
        $this->assertEquals([], $input->arguments);
        $this->assertEquals(['dave', 'young'], $input->options['opt']);
    }

    public function testCompilingArrayLongOptionWithNoValueSetsValueToAnEmptyArray(): void
    {
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [
                    new Option('opt', OptionType::IsArray, null, '')
                ],
                ''
            ),
            $commandHandler::class
        );
        $input = $this->compiler->compile('foo --opt');
        $this->assertSame('foo', $input->commandName);
        $this->assertEquals([], $input->arguments);
        $this->assertEquals([], $input->options['opt']);
    }

    public function testCompilingArrayLongOptionWithoutEqualsSign(): void
    {
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [
                    new Option('opt', OptionType::IsArray, null, '')
                ],
                ''
            ),
            $commandHandler::class
        );
        $input = $this->compiler->compile('foo --opt dave --opt young');
        $this->assertSame('foo', $input->commandName);
        $this->assertEquals([], $input->arguments);
        $this->assertEquals(['dave', 'young'], $input->options['opt']);
    }

    public function testCompilingArrayLongOptionWithSingleValueConvertsValueToAnArray(): void
    {
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [
                    new Option('opt', OptionType::IsArray, null, '')
                ],
                ''
            ),
            $commandHandler::class
        );
        $input = $this->compiler->compile('foo --opt=dave');
        $this->assertSame('foo', $input->commandName);
        $this->assertEquals([], $input->arguments);
        $this->assertEquals(['dave'], $input->options['opt']);
    }

    public function testCompilingCommandName(): void
    {
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [],
                ''
            ),
            $commandHandler::class
        );
        $input = $this->compiler->compile('foo');
        $this->assertSame('foo', $input->commandName);
        $this->assertEquals([], $input->arguments);
        $this->assertEquals([], $input->options);
    }

    public function testCompilingLongOptionWithEqualsSign(): void
    {
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [new Option('opt', OptionType::RequiredValue, null, '')],
                ''
            ),
            $commandHandler::class
        );
        $input = $this->compiler->compile('foo --opt=dave');
        $this->assertSame('foo', $input->commandName);
        $this->assertEquals([], $input->arguments);
        $this->assertSame('dave', $input->options['opt']);
    }

    public function testCompilingLongOptionWithoutEqualsSign(): void
    {
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [new Option('opt', OptionType::RequiredValue, null, '')],
                ''
            ),
            $commandHandler::class
        );
        $input = $this->compiler->compile('foo --opt dave');
        $this->assertSame('foo', $input->commandName);
        $this->assertEquals([], $input->arguments);
        $this->assertSame('dave', $input->options['opt']);
    }

    public function testCompilingLongOptionWithoutEqualsSignWithArgumentAfter(): void
    {
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(
            new Command(
                'foo',
                [new Argument('arg', ArgumentType::Required, '')],
                [new Option('opt', OptionType::RequiredValue, null, '')],
                ''
            ),
            $commandHandler::class
        );
        $input = $this->compiler->compile('foo --opt dave bar');
        $this->assertSame('foo', $input->commandName);
        $this->assertSame('bar', $input->arguments['arg']);
        $this->assertSame('dave', $input->options['opt']);
    }

    public function testCompilingLongOptionWithoutEqualsSignWithQuotedValue(): void
    {
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [
                    new Option('opt1', OptionType::RequiredValue, null, ''),
                    new Option('opt2', OptionType::RequiredValue, null, '')
                ],
                ''
            ),
            $commandHandler::class
        );
        $input = $this->compiler->compile("foo --opt1 'dave' --opt2=\"young\"");
        $this->assertSame('foo', $input->commandName);
        $this->assertEquals([], $input->arguments);
        $this->assertSame('dave', $input->options['opt1']);
        $this->assertSame('young', $input->options['opt2']);
    }


    public function testCompilingMultipleArgument(): void
    {
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(
            new Command(
                'foo',
                [
                    new Argument('arg1', ArgumentType::Optional, ''),
                    new Argument('arg2', ArgumentType::Optional, ''),
                    new Argument('arg3', ArgumentType::Optional, '')
                ],
                [],
                ''
            ),
            $commandHandler::class
        );
        $input = $this->compiler->compile('foo bar baz blah');
        $this->assertSame('foo', $input->commandName);
        $this->assertSame('bar', $input->arguments['arg1']);
        $this->assertSame('baz', $input->arguments['arg2']);
        $this->assertSame('blah', $input->arguments['arg3']);
        $this->assertEquals([], $input->options);
    }

    public function testCompilingMultipleSeparateShortOptions(): void
    {
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [
                    new Option('opt1', OptionType::NoValue, 'r', ''),
                    new Option('opt2', OptionType::NoValue, 'f', ''),
                    new Option('opt3', OptionType::NoValue, 'd', '')
                ],
                ''
            ),
            $commandHandler::class
        );
        $input = $this->compiler->compile('foo -r -f -d');
        $this->assertSame('foo', $input->commandName);
        $this->assertNull($input->options['opt1']);
        $this->assertNull($input->options['opt2']);
        $this->assertNull($input->options['opt3']);
        $this->assertEquals([], $input->arguments);
    }

    public function testCompilingMultipleShortOptions(): void
    {
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [
                    new Option('opt1', OptionType::NoValue, 'r', ''),
                    new Option('opt2', OptionType::NoValue, 'f', ''),
                    new Option('opt3', OptionType::NoValue, 'd', '')
                ],
                ''
            ),
            $commandHandler::class
        );
        $input = $this->compiler->compile('foo -rfd');
        $this->assertSame('foo', $input->commandName);
        $this->assertNull($input->options['opt1']);
        $this->assertNull($input->options['opt2']);
        $this->assertNull($input->options['opt3']);
        $this->assertEquals([], $input->arguments);
    }

    public function testCompilingNoValueOptionWithValueThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [new Option('opt', OptionType::NoValue, null, '')],
                '',
                ''
            ),
            $commandHandler::class
        );
        $this->compiler->compile('foo --opt=bar');
    }

    public function testCompilingOptionalArgumentWithDefaultValueUsesDefaultValueWhenNoValueIsPassedIn(): void
    {
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(
            new Command(
                'foo',
                [new Argument('arg', ArgumentType::Optional, '', 'bar')],
                [],
                '',
                ''
            ),
            $commandHandler::class
        );
        $input = $this->compiler->compile('foo');
        $this->assertSame('bar', $input->arguments['arg']);
    }

    public function testCompilingOptionWithOptionalValueWithDefaultValueUsesDefaultValueWhenNoValueIsPassedIn(): void
    {
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [new Option('opt', OptionType::OptionalValue, defaultValue: 'bar')],
                '',
                ''
            ),
            $commandHandler::class
        );
        $input = $this->compiler->compile('foo --opt');
        $this->assertSame('bar', $input->options['opt']);
    }

    public function testCompilingRequiredArgumentsWithoutSpecifyingAllValuesThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(
            new Command(
                'foo',
                [
                    new Argument('arg1', ArgumentType::Required, ''),
                    new Argument('arg2', ArgumentType::Required, '')
                ]
            ),
            $commandHandler::class
        );
        $this->compiler->compile('foo bar');
    }

    public function testCompilingRequiredValueOptionWithoutValueThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [new Option('opt', OptionType::RequiredValue, null, '')],
                '',
                ''
            ),
            $commandHandler::class
        );
        $this->compiler->compile('foo --opt');
    }

    public function testCompilingSingleArgument(): void
    {
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(
            new Command(
                'foo',
                [new Argument('arg', ArgumentType::Required, '')],
                [],
                ''
            ),
            $commandHandler::class
        );
        $input = $this->compiler->compile('foo bar');
        $this->assertSame('foo', $input->commandName);
        $this->assertSame('bar', $input->arguments['arg']);
        $this->assertEquals([], $input->options);
    }

    public function testCompilingSingleShortOption(): void
    {
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [new Option('opt', OptionType::NoValue, 'r', '')],
                ''
            ),
            $commandHandler::class
        );
        $input = $this->compiler->compile('foo -r');
        $this->assertSame('foo', $input->commandName);
        $this->assertNull($input->options['opt']);
        $this->assertEquals([], $input->arguments);
    }

    public function testCompilingTwoConsecutiveLongOptions(): void
    {
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [
                    new Option('opt1', OptionType::NoValue, null, ''),
                    new Option('opt2', OptionType::NoValue, null, '')
                ],
                ''
            ),
            $commandHandler::class
        );
        $input = $this->compiler->compile('foo --opt1 --opt2');
        $this->assertSame('foo', $input->commandName);
        $this->assertEquals([], $input->arguments);
        $this->assertEquals(null, $input->options['opt1']);
        $this->assertEquals(null, $input->options['opt2']);
    }

    public function testCompilingStringInputIsCompiledCorrectly(): void
    {
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(new Command('foo'), $commandHandler::class);
        $input = $this->compiler->compile('foo');
        $this->assertSame('foo', $input->commandName);
    }

    public function testCompilingUnregisteredCommandThrowsException(): void
    {
        $this->expectException(CommandNotFoundException::class);
        $this->compiler->compile('foo');
    }

    public function testCompilingUsesDefaultValuesForOptionsThatAreNotSet(): void
    {
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(
            new Command(
                'foo',
                [],
                [
                    new Option('foo', OptionType::RequiredValue, 'f', '', 'foo value'),
                    new Option('bar', OptionType::OptionalValue, 'b', '', 'bar value'),
                    new Option('baz', OptionType::NoValue, 'z', 'Baz command', 'baz value')
                ],
                '',
                ''
            ),
            $commandHandler::class
        );
        $input = $this->compiler->compile('foo');
        $this->assertSame('foo value', $input->options['foo']);
        $this->assertSame('bar value', $input->options['bar']);
        $this->assertFalse(isset($input->options['baz']));
    }

    public function testCompilingWithTooManyArgumentsThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(
            new Command(
                'foo',
                [new Argument('arg', ArgumentType::Required, '')],
                [],
                ''
            ),
            $commandHandler::class
        );
        $this->compiler->compile('foo bar baz');
    }
}
