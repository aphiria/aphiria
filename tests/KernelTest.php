<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Input\Argument;
use Aphiria\Console\Input\ArgumentTypes;
use Aphiria\Console\Input\Compilers\IInputCompiler;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Input\Option;
use Aphiria\Console\Input\OptionTypes;
use Aphiria\Console\Kernel;
use Aphiria\Console\Output\IOutput;
use Aphiria\Console\StatusCodes;
use Aphiria\Console\Tests\Output\Mocks\Output;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Tests the console kernel
 */
class KernelTest extends TestCase
{
    /** @var IInputCompiler|MockObject */
    private $inputCompiler;
    /** @var CommandRegistry */
    private $commands;
    /** @var Output */
    private $output;
    /** @var Kernel */
    private $kernel;

    protected function setUp(): void
    {
        $this->commands = new CommandRegistry();
        $this->inputCompiler = $this->createMock(IInputCompiler::class);
        $this->kernel = new Kernel($this->commands, $this->inputCompiler);
        $this->output = new Output();
    }

    public function testHandlingException(): void
    {
        $this->inputCompiler->method('compile')
            ->with("unclosed quote '")
            ->willThrowException(new RuntimeException());
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
        $this->inputCompiler->expects($this->at(0))
            ->method('compile')
            ->with('help holiday')
            ->willReturn(new Input('help', ['command' => 'holiday'], []));
        $this->inputCompiler->expects($this->at(1))
            ->method('compile')
            ->with('help')
            ->willReturn(new Input('help', [], []));
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
        $this->inputCompiler->method('compile')
            ->with('help fake')
            ->willReturn(new Input('help', ['command' => 'fake'], []));
        ob_start();
        $status = $this->kernel->handle('help fake', $this->output);
        ob_end_clean();
        $this->assertEquals(StatusCodes::ERROR, $status);
    }

    public function testHandlingHolidayCommand(): void
    {
        $this->inputCompiler->expects($this->at(0))
            ->method('compile')
            ->with('holiday birthday -y')
            ->willReturn(new Input('holiday', ['holiday' => 'birthday'], ['yell' => 'yes']));
        $this->inputCompiler->expects($this->at(1))
            ->method('compile')
            ->with('holiday Easter --yell=no')
            ->willReturn(new Input('holiday', ['holiday' => 'Easter'], ['yell' => 'no']));
        // Test with short option
        $this->commands->registerCommand(
            new Command(
                'holiday',
                [new Argument('holiday', ArgumentTypes::REQUIRED, '')],
                [new Option('yell', 'y', OptionTypes::OPTIONAL_VALUE, '', 'yes')],
                ''
            ),
            function (Input $input, IOutput $output) {
                $message = 'Happy ' . $input->arguments['holiday'];

                if ($input->options['yell'] === 'yes') {
                    $message .= '!';
                }

                $output->write($message);
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

    public function testHandlingInvalidInputThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->kernel->handle($this, $this->output);
    }

    public function testHandlingMissingCommand(): void
    {
        $this->inputCompiler->method('compile')
            ->with('fake')
            ->willThrowException(new InvalidArgumentException());
        ob_start();
        $status = $this->kernel->handle('fake', $this->output);
        ob_get_clean();
        $this->assertEquals(StatusCodes::ERROR, $status);
    }

    public function testHandlingSimpleCommand(): void
    {
        $this->inputCompiler->method('compile')
            ->with('foo')
            ->willReturn(new Input('foo', [], []));
        $this->commands->registerCommand(
            new Command('foo', [], [], ''),
            function (Input $input, IOutput $output) {
                $output->write('foo');
            }
        );
        ob_start();
        $status = $this->kernel->handle('foo', $this->output);
        $this->assertEquals('foo', ob_get_clean());
        $this->assertEquals(StatusCodes::OK, $status);
    }

    public function testHandlingWithHandlerThatDoesNotReturnAnythingDefaultsToOk(): void
    {
        $this->inputCompiler->method('compile')
            ->with('foo')
            ->willReturn(new Input('foo', [], []));
        $command = new Command('foo', [], [], '');
        $commandHandler = function (Input $input, IOutput $output) {
            $this->assertSame($this->output, $output);
        };
        $this->commands->registerCommand($command, $commandHandler);
        $statusCode = $this->kernel->handle('foo', $this->output);
        $this->assertEquals(StatusCodes::OK, $statusCode);
    }
}
