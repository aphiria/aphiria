<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests;

use Aphiria\Console\Application;
use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Input\Argument;
use Aphiria\Console\Input\ArgumentTypes;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Input\Option;
use Aphiria\Console\Input\OptionTypes;
use Aphiria\Console\Output\IOutput;
use Aphiria\Console\StatusCodes;
use Aphiria\Console\Tests\Output\Mocks\Output;
use PHPUnit\Framework\TestCase;

/**
 * Tests the console application
 */
class ApplicationTest extends TestCase
{
    private CommandRegistry $commands;
    private Output $output;
    private Application $app;

    protected function setUp(): void
    {
        $this->commands = new CommandRegistry();
        $this->app = new Application($this->commands);
        $this->output = new Output();
    }

    public function testHandlingEmptyCommandReturnsOk(): void
    {
        ob_start();
        $status = $this->app->handle('', $this->output);
        ob_get_clean();
        $this->assertEquals(StatusCodes::OK, $status);
    }

    public function testHandlingException(): void
    {
        ob_start();
        $status = $this->app->handle("unclosed quote '", $this->output);
        ob_end_clean();
        $this->assertEquals(StatusCodes::FATAL, $status);
    }

    public function testHandlingHelpCommand(): void
    {
        // Try with command name
        $this->commands->registerCommand(
            new Command('holiday', [], [], ''),
            fn (Input $input, IOutput $output) => null
        );
        ob_start();
        $status = $this->app->handle('help holiday', $this->output);
        ob_get_clean();
        $this->assertEquals(StatusCodes::OK, $status);

        // Try with command name with no argument
        ob_start();
        $status = $this->app->handle('help', $this->output);
        ob_get_clean();
        $this->assertEquals(StatusCodes::OK, $status);
    }

    public function testHandlingHelpCommandWithNonExistentCommand(): void
    {
        ob_start();
        $status = $this->app->handle('help fake', $this->output);
        ob_end_clean();
        $this->assertEquals(StatusCodes::ERROR, $status);
    }

    public function testHandlingHolidayCommand(): void
    {
        // Test with short option
        $this->commands->registerCommand(
            new Command(
                'holiday',
                [new Argument('holiday', ArgumentTypes::REQUIRED, '')],
                [new Option('yell', 'y', OptionTypes::OPTIONAL_VALUE, '', 'yes')],
                ''
            ),
            fn () => function (Input $input, IOutput $output) {
                $message = 'Happy ' . $input->arguments['holiday'];

                if ($input->options['yell'] === 'yes') {
                    $message .= '!';
                }

                $output->write($message);
            }
        );
        ob_start();
        $status = $this->app->handle('holiday birthday -y', $this->output);
        $this->assertEquals('Happy birthday!', ob_get_clean());
        $this->assertEquals(StatusCodes::OK, $status);

        // Test with long option
        ob_start();
        $status = $this->app->handle('holiday Easter --yell=no', $this->output);
        $this->assertEquals('Happy Easter', ob_get_clean());
        $this->assertEquals(StatusCodes::OK, $status);
    }

    public function testHandlingInvalidInputReturnsError(): void
    {
        ob_start();
        $status = $this->app->handle($this, $this->output);
        ob_end_clean();
        $this->assertEquals(StatusCodes::ERROR, $status);
    }

    public function testHandlingMissingCommandReturnsError(): void
    {
        ob_start();
        $status = $this->app->handle('fake', $this->output);
        ob_get_clean();
        $this->assertEquals(StatusCodes::ERROR, $status);
    }

    public function testHandlingSimpleCommand(): void
    {
        $this->commands->registerCommand(
            new Command('foo', [], [], ''),
            fn () => fn (Input $input, IOutput $output) => $output->write('foo')
        );
        ob_start();
        $status = $this->app->handle('foo', $this->output);
        $this->assertEquals('foo', ob_get_clean());
        $this->assertEquals(StatusCodes::OK, $status);
    }

    public function testHandlingWithHandlerThatDoesNotReturnAnythingDefaultsToOk(): void
    {
        $command = new Command('foo', [], [], '');
        $commandHandlerFactory = fn () => fn (Input $input, IOutput $output) => $this->assertSame($this->output, $output);
        $this->commands->registerCommand($command, $commandHandlerFactory);
        $statusCode = $this->app->handle('foo', $this->output);
        $this->assertEquals(StatusCodes::OK, $statusCode);
    }
}
