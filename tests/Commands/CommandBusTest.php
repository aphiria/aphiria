<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Commands;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandBinding;
use Aphiria\Console\Commands\CommandBindingRegistry;
use Aphiria\Console\Commands\CommandBus;
use Aphiria\Console\Commands\CommandInput;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;
use Aphiria\Console\StatusCodes;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the command bus
 */
class CommandBusTest extends TestCase
{
    /** @var CommandBindingRegistry */
    private $commandBindings;
    /** @var CommandBus */
    private $commandBus;
    /** @var IOutput|MockObject */
    private $output;

    public function setUp(): void
    {
        $this->commandBindings = new CommandBindingRegistry();
        $this->output = $this->createMock(IOutput::class);
        $this->commandBus = new CommandBus($this->commandBindings);
    }

    public function testHandlingWithHandlerThatDoesNotReturnAnythingDefaultsToOk(): void
    {
        $command = new Command('foo', [], [], '');
        $commandHandler = function (CommandInput $commandInput, IOutput $output) {
            $this->assertSame($this->output, $output);
        };
        $input = new Input('foo', [], []);
        $this->commandBindings->registerCommandBinding(
            new CommandBinding($command, $commandHandler)
        );
        $statusCode = $this->commandBus->handle($input, $this->output);
        $this->assertEquals(StatusCodes::OK, $statusCode);
    }

    public function testHandlingClosureHandlerIsInvoked(): void
    {
        $command = new Command('foo', [], [], '');
        $closureWasInvoked = false;
        $commandHandler = function (CommandInput $commandInput, IOutput $output) use (&$closureWasInvoked) {
            $this->assertSame($this->output, $output);
            $closureWasInvoked = true;

            return StatusCodes::OK;
        };
        $input = new Input('foo', [], []);
        $this->commandBindings->registerCommandBinding(
            new CommandBinding($command, $commandHandler)
        );
        $statusCode = $this->commandBus->handle($input, $this->output);
        $this->assertEquals(StatusCodes::OK, $statusCode);
        $this->assertTrue($closureWasInvoked);
    }

    public function testHandlingCommandHandlerIsInvoked(): void
    {
        $command = new Command('foo', [], [], '');
        $handlerWasInvoked = false;
        $commandHandler = new class($handlerWasInvoked) implements ICommandHandler {
            private $handlerWasInvoked;

            public function __construct(bool &$handlerWasInvoked)
            {
                $this->handlerWasInvoked = &$handlerWasInvoked;
            }

            public function handle(CommandInput $commandInput, IOutput $output)
            {
                $this->handlerWasInvoked = true;

                return StatusCodes::OK;
            }
        };
        $input = new Input('foo', [], []);
        $this->commandBindings->registerCommandBinding(
            new CommandBinding($command, $commandHandler)
        );
        $statusCode = $this->commandBus->handle($input, $this->output);
        $this->assertEquals(StatusCodes::OK, $statusCode);
        $this->assertTrue($handlerWasInvoked);
    }
}
