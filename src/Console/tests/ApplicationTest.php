<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests;

use Aphiria\Console\Application;
use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\Defaults\AboutCommandHandler;
use Aphiria\Console\Commands\Defaults\HelpCommandHandler;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Input\Argument;
use Aphiria\Console\Input\ArgumentType;
use Aphiria\Console\Input\Compilers\IInputCompiler;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Input\Option;
use Aphiria\Console\Input\OptionType;
use Aphiria\Console\Output\IOutput;
use Aphiria\Console\StatusCode;
use Aphiria\Console\Tests\Output\Mocks\Output;
use Aphiria\DependencyInjection\IServiceResolver;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Throwable;

class ApplicationTest extends TestCase
{
    private CommandRegistry $commands;
    private IServiceResolver&MockObject $commandHandlerResolver;
    private Output $output;
    private Application $app;

    protected function setUp(): void
    {
        $this->commands = new CommandRegistry();
        $this->commandHandlerResolver = $this->createMock(IServiceResolver::class);
        $this->app = new Application($this->commands, $this->commandHandlerResolver);
        $this->output = new Output();
    }

    public function testHandlingAboutCommandWithNoHandlerThrowsException(): void
    {
        $output = $this->createMock(IOutput::class);
        $output->expects($this->once())
            ->method('writeln')
            ->with('<fatal>About command not registered</fatal>')
            ->willReturnArgument(0);
        // Purposely use new commands that don't have anything registered to them
        $app = new class (new CommandRegistry(), $this->commandHandlerResolver) extends Application {
            protected function formatExceptionMessage(Exception|Throwable $ex): string
            {
                // Simplify testing
                return $ex->getMessage();
            }

            protected function registerDefaultCommands(): void
            {
                // Simulate overriding and not registering any commands
            }
        };
        $status = $app->handle('', $output);
        $this->assertSame(StatusCode::Fatal, $status);
    }

    public function testHandlingCommandWithNoHandlerThrowsException(): void
    {
        // The default input compiler protects us from compiling commands without handlers
        // So, use a custom one for this test
        $inputCompiler = $this->createMock(IInputCompiler::class);
        $inputCompiler->expects($this->once())
            ->method('compile')
            ->with('foo')
            ->willReturn(new Input('foo', [], []));
        $output = $this->createMock(IOutput::class);
        $output->expects($this->once())
            ->method('writeln')
            ->with('<error>Command "foo" is not registered</error>')
            ->willReturnArgument(0);
        $app = new class ($this->commands, $this->commandHandlerResolver, $inputCompiler) extends Application {
            protected function formatExceptionMessage(Exception|Throwable $ex): string
            {
                // Simplify testing
                return $ex->getMessage();
            }
        };
        $status = $app->handle('foo', $output);
        $this->assertSame(StatusCode::Error, $status);
    }

    public function testHandlingEmptyCommandReturnsOk(): void
    {
        $this->commandHandlerResolver->expects($this->once())
            ->method('resolve')
            ->with(AboutCommandHandler::class)
            ->willReturn(new AboutCommandHandler($this->commands));
        \ob_start();
        $status = $this->app->handle('', $this->output);
        \ob_get_clean();
        $this->assertSame(StatusCode::Ok, $status);
    }

    public function testHandlingException(): void
    {
        \ob_start();
        $status = $this->app->handle("unclosed quote '", $this->output);
        \ob_end_clean();
        $this->assertSame(StatusCode::Fatal, $status);
    }

    public function testHandlingHelpCommand(): void
    {
        $this->commandHandlerResolver->expects($this->exactly(2))
            ->method('resolve')
            ->with(HelpCommandHandler::class)
            ->willReturn(new HelpCommandHandler($this->commands));
        // Try with command name
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand(new Command('holiday', [], [], ''), $commandHandler::class);
        \ob_start();
        $status = $this->app->handle('help holiday', $this->output);
        \ob_get_clean();
        $this->assertSame(StatusCode::Ok, $status);

        // Try with command name with no argument
        \ob_start();
        $status = $this->app->handle('help', $this->output);
        \ob_get_clean();
        $this->assertSame(StatusCode::Ok, $status);
    }

    public function testHandlingHelpCommandWithNonExistentCommand(): void
    {
        $this->commandHandlerResolver->expects($this->once())
            ->method('resolve')
            ->with(HelpCommandHandler::class)
            ->willReturn(new HelpCommandHandler($this->commands));
        \ob_start();
        $status = $this->app->handle('help fake', $this->output);
        \ob_end_clean();
        $this->assertSame(StatusCode::Error, $status);
    }

    public function testHandlingHolidayCommand(): void
    {
        $commandHandler = new class () implements ICommandHandler {
            /**
             * @inheritdoc
             *
             * @return void
             */
            public function handle(Input $input, IOutput $output)
            {
                $message = "Happy {$input->arguments['holiday']}";

                if ($input->options['yell'] === 'yes') {
                    $message .= '!';
                }

                $output->write($message);
            }
        };
        $this->commandHandlerResolver->expects($this->exactly(2))
            ->method('resolve')
            ->with($commandHandler::class)
            ->willReturn($commandHandler);

        // Test with short option
        $this->commands->registerCommand(
            new Command(
                'holiday',
                [new Argument('holiday', ArgumentType::Required, '')],
                [new Option('yell', OptionType::OptionalValue, 'y', '', 'yes')],
                ''
            ),
            $commandHandler::class
        );
        \ob_start();
        $status = $this->app->handle('holiday birthday -y', $this->output);
        $this->assertSame('Happy birthday!', \ob_get_clean());
        $this->assertSame(StatusCode::Ok, $status);

        // Test with long option
        \ob_start();
        $status = $this->app->handle('holiday Easter --yell=no', $this->output);
        $this->assertSame('Happy Easter', \ob_get_clean());
        $this->assertSame(StatusCode::Ok, $status);
    }

    public function testHandlingMissingCommandReturnsError(): void
    {
        \ob_start();
        $status = $this->app->handle('fake', $this->output);
        \ob_get_clean();
        $this->assertSame(StatusCode::Error, $status);
    }

    public function testHandlingSimpleCommand(): void
    {
        $commandHandler = new class () implements ICommandHandler {
            /**
             * @inheritdoc
             *
             * @return void
             */
            public function handle(Input $input, IOutput $output)
            {
                $output->write('foo');
            }
        };
        $this->commandHandlerResolver->expects($this->once())
            ->method('resolve')
            ->with($commandHandler::class)
            ->willReturn($commandHandler);
        $this->commands->registerCommand(new Command('foo'), $commandHandler::class);
        \ob_start();
        $status = $this->app->handle('foo', $this->output);
        $this->assertSame('foo', \ob_get_clean());
        $this->assertSame(StatusCode::Ok, $status);
    }

    public function testHandlingWithHandlerThatDoesNotReturnAnythingDefaultsToOk(): void
    {
        $commandHandler = new class () implements ICommandHandler {
            /**
             * @inheritdoc
             *
             * @return void
             */
            public function handle(Input $input, IOutput $output)
            {
                // Don't do anything
            }
        };
        $this->commandHandlerResolver->expects($this->once())
            ->method('resolve')
            ->with($commandHandler::class)
            ->willReturn($commandHandler);
        $this->commands->registerCommand(new Command('foo'), $commandHandler::class);
        $statusCode = $this->app->handle('foo', $this->output);
        $this->assertSame(StatusCode::Ok, $statusCode);
    }
}
