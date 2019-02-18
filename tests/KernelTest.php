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
use Aphiria\Console\Commands\CommandHandlerBinding;
use Aphiria\Console\Commands\CommandHandlerBindingRegistry;
use Aphiria\Console\Commands\CommandInput;
use Aphiria\Console\Kernel;
use Aphiria\Console\Requests\Argument;
use Aphiria\Console\Requests\ArgumentTypes;
use Aphiria\Console\Requests\Compilers\StringRequestCompiler;
use Aphiria\Console\Requests\Option;
use Aphiria\Console\Requests\OptionTypes;
use Aphiria\Console\Responses\IResponse;
use Aphiria\Console\StatusCodes;
use Aphiria\Console\Tests\Responses\Mocks\Response;
use PHPUnit\Framework\TestCase;

/**
 * Tests the console kernel
 */
class KernelTest extends TestCase
{
    /** @var StringRequestCompiler */
    private $requestCompiler;
    /** @var CommandHandlerBindingRegistry */
    private $commandHandlerBindings;
    /** @var Response */
    private $response;
    /** @var Kernel */
    private $kernel;

    public function setUp(): void
    {
        $this->commandHandlerBindings = new CommandHandlerBindingRegistry();
        $this->requestCompiler = new StringRequestCompiler();
        $this->kernel = new Kernel($this->commandHandlerBindings, $this->requestCompiler);
        $this->response = new Response();
    }

    public function testHandlingException(): void
    {
        ob_start();
        $status = $this->kernel->handle("unclosed quote '", $this->response);
        ob_end_clean();
        $this->assertEquals(StatusCodes::FATAL, $status);
    }

    public function testHandlingHelpCommand(): void
    {
        // Try with command name
        $this->commandHandlerBindings->registerCommandHandlerBinding(new CommandHandlerBinding(
            new Command('holiday', [], [], ''),
            function (CommandInput $commandInput, IResponse $response) {
                // Don't do anything
            }
        ));
        ob_start();
        $status = $this->kernel->handle('help holiday', $this->response);
        ob_get_clean();
        $this->assertEquals(StatusCodes::OK, $status);

        // Try with command name with no argument
        ob_start();
        $status = $this->kernel->handle('help', $this->response);
        ob_get_clean();
        $this->assertEquals(StatusCodes::OK, $status);
    }

    public function testHandlingHelpCommandWithNonExistentCommand(): void
    {
        ob_start();
        $status = $this->kernel->handle('help fake', $this->response);
        ob_end_clean();
        $this->assertEquals(StatusCodes::ERROR, $status);
    }

    public function testHandlingHolidayCommand(): void
    {
        // Test with short option
        $this->commandHandlerBindings->registerCommandHandlerBinding(new CommandHandlerBinding(
            new Command(
                'holiday',
                [new Argument('holiday', ArgumentTypes::REQUIRED, '')],
                [new Option('yell', 'y', OptionTypes::OPTIONAL_VALUE, '', 'yes')],
                ''
            ),
            function (CommandInput $commandInput, IResponse $response) {
                $message = 'Happy ' . $commandInput->arguments['holiday'];

                if ($commandInput->options['yell'] === 'yes') {
                    $message .= '!';
                }

                $response->write($message);
            }
        ));
        ob_start();
        $status = $this->kernel->handle('holiday birthday -y', $this->response);
        $this->assertEquals('Happy birthday!', ob_get_clean());
        $this->assertEquals(StatusCodes::OK, $status);

        // Test with long option
        ob_start();
        $status = $this->kernel->handle('holiday Easter --yell=no', $this->response);
        $this->assertEquals('Happy Easter', ob_get_clean());
        $this->assertEquals(StatusCodes::OK, $status);
    }

    public function testHandlingMissingCommand(): void
    {
        ob_start();
        $status = $this->kernel->handle('fake', $this->response);
        ob_get_clean();
        $this->assertEquals(StatusCodes::ERROR, $status);
    }

    public function testHandlingSimpleCommand(): void
    {
        $this->commandHandlerBindings->registerCommandHandlerBinding(new CommandHandlerBinding(
            new Command('foo', [], [], ''),
            function (CommandInput $commandInput, IResponse $response) {
                $response->write('foo');
            }
        ));
        ob_start();
        $status = $this->kernel->handle('foo', $this->response);
        $this->assertEquals('foo', ob_get_clean());
        $this->assertEquals(StatusCodes::OK, $status);
    }
}
