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
use Aphiria\Console\Commands\CommandBus;
use Aphiria\Console\Commands\CommandHandlerBinding;
use Aphiria\Console\Commands\CommandHandlerBindingRegistry;
use Aphiria\Console\Commands\CommandInput;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Requests\Request;
use Aphiria\Console\Responses\IResponse;
use Aphiria\Console\StatusCodes;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the command bus
 */
class CommandBusTest extends TestCase
{
    /** @var CommandHandlerBindingRegistry */
    private $commandHandlerBindings;
    /** @var CommandBus */
    private $commandBus;
    /** @var IResponse|MockObject */
    private $response;

    public function setUp(): void
    {
        $this->commandHandlerBindings = new CommandHandlerBindingRegistry();
        $this->response = $this->createMock(IResponse::class);
        $this->commandBus = new CommandBus($this->commandHandlerBindings);
    }

    public function testHandlingWithHandlerThatDoesNotReturnAnythingDefaultsToOk(): void
    {
        $command = new Command('foo', [], [], '');
        $commandHandler = function (CommandInput $commandInput, IResponse $response) {
            $this->assertSame($this->response, $response);
        };
        $request = new Request('foo', [], []);
        $this->commandHandlerBindings->registerCommandHandlerBinding(
            new CommandHandlerBinding($command, $commandHandler)
        );
        $statusCode = $this->commandBus->handle($request, $this->response);
        $this->assertEquals(StatusCodes::OK, $statusCode);
    }

    public function testHandlingClosureHandlerIsInvoked(): void
    {
        $command = new Command('foo', [], [], '');
        $closureWasInvoked = false;
        $commandHandler = function (CommandInput $commandInput, IResponse $response) use (&$closureWasInvoked) {
            $this->assertSame($this->response, $response);
            $closureWasInvoked = true;

            return StatusCodes::OK;
        };
        $request = new Request('foo', [], []);
        $this->commandHandlerBindings->registerCommandHandlerBinding(
            new CommandHandlerBinding($command, $commandHandler)
        );
        $statusCode = $this->commandBus->handle($request, $this->response);
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

            public function handle(CommandInput $commandInput, IResponse $response)
            {
                $this->handlerWasInvoked = true;

                return StatusCodes::OK;
            }
        };
        $request = new Request('foo', [], []);
        $this->commandHandlerBindings->registerCommandHandlerBinding(
            new CommandHandlerBinding($command, $commandHandler)
        );
        $statusCode = $this->commandBus->handle($request, $this->response);
        $this->assertEquals(StatusCodes::OK, $statusCode);
        $this->assertTrue($handlerWasInvoked);
    }
}
