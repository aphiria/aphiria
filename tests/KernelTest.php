<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Input\Argument;
use Aphiria\Console\Input\ArgumentTypes;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Input\Option;
use Aphiria\Console\Input\OptionTypes;
use Aphiria\Console\Kernel;
use Aphiria\Console\Output\IOutput;
use Aphiria\Console\StatusCodes;
use Aphiria\Console\Tests\Output\Mocks\Output;
use PHPUnit\Framework\TestCase;

/**
 * Tests the console kernel
 */
class KernelTest extends TestCase
{
    private CommandRegistry $commands;
    private Output $output;
    private Kernel $kernel;

    protected function setUp(): void
    {
        $this->commands = new CommandRegistry();
        $this->kernel = new Kernel($this->commands);
        $this->output = new Output();
    }

    public function testHandlingEmptyCommandReturnsOk(): void
    {
        ob_start();
        $status = $this->kernel->handle('', $this->output);
        ob_get_clean();
        $this->assertEquals(StatusCodes::OK, $status);
    }

    public function testHandlingException(): void
    {
        ob_start();
        $status = $this->kernel->handle("unclosed quote '", $this->output);
        ob_end_clean();
        $this->assertEquals(StatusCodes::FATAL, $status);
    }

    public function testHandlingHelpCommand(): void
    {
        // Try with command name
        $this->commands->registerCommand(
            new Command('holiday', [], [], ''),
            function (Input $input, IOutput $output) {
                // Don't do anything
            }
        );
        ob_start();
        $status = $this->kernel->handle('help holiday', $this->output);
        ob_get_clean();
        $this->assertEquals(StatusCodes::OK, $status);

        // Try with command name with no argument
        ob_start();
        $status = $this->kernel->handle('help', $this->output);
        ob_get_clean();
        $this->assertEquals(StatusCodes::OK, $status);
    }

    public function testHandlingHelpCommandWithNonExistentCommand(): void
    {
        ob_start();
        $status = $this->kernel->handle('help fake', $this->output);
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
            function () {
                return function (Input $input, IOutput $output) {
                    $message = 'Happy ' . $input->arguments['holiday'];

                    if ($input->options['yell'] === 'yes') {
                        $message .= '!';
                    }

                    $output->write($message);
                };
            }
        );
        ob_start();
        $status = $this->kernel->handle('holiday birthday -y', $this->output);
        $this->assertEquals('Happy birthday!', ob_get_clean());
        $this->assertEquals(StatusCodes::OK, $status);

        // Test with long option
        ob_start();
        $status = $this->kernel->handle('holiday Easter --yell=no', $this->output);
        $this->assertEquals('Happy Easter', ob_get_clean());
        $this->assertEquals(StatusCodes::OK, $status);
    }

    public function testHandlingInvalidInputReturnsError(): void
    {
        ob_start();
        $status = $this->kernel->handle($this, $this->output);
        ob_end_clean();
        $this->assertEquals(StatusCodes::ERROR, $status);
    }

    public function testHandlingMissingCommandReturnsError(): void
    {
        ob_start();
        $status = $this->kernel->handle('fake', $this->output);
        ob_get_clean();
        $this->assertEquals(StatusCodes::ERROR, $status);
    }

    public function testHandlingSimpleCommand(): void
    {
        $this->commands->registerCommand(
            new Command('foo', [], [], ''),
            function () {
                return function (Input $input, IOutput $output) {
                    $output->write('foo');
                };
            }
        );
        ob_start();
        $status = $this->kernel->handle('foo', $this->output);
        $this->assertEquals('foo', ob_get_clean());
        $this->assertEquals(StatusCodes::OK, $status);
    }

    public function testHandlingWithHandlerThatDoesNotReturnAnythingDefaultsToOk(): void
    {
        $command = new Command('foo', [], [], '');
        $commandHandlerFactory = function () {
            return function (Input $input, IOutput $output) {
                $this->assertSame($this->output, $output);
            };
        };
        $this->commands->registerCommand($command, $commandHandlerFactory);
        $statusCode = $this->kernel->handle('foo', $this->output);
        $this->assertEquals(StatusCodes::OK, $statusCode);
    }
}
