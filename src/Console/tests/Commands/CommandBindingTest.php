<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Commands;

use Aphiria\Console\Commands\ClosureCommandHandler;
use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandBinding;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Tests the command binding
 */
class CommandBindingTest extends TestCase
{
    public function testPropertiesAreSetInConstructorWhenUsingCommandHandlerInterface(): void
    {
        $expectedCommand = new Command('name', [], [], '', '');
        $expectedCommandHandlerFactory = fn () => $this->createMock(ICommandHandler::class);
        $binding = new CommandBinding($expectedCommand, $expectedCommandHandlerFactory);
        $this->assertSame($expectedCommand, $binding->command);
        $this->assertSame($expectedCommandHandlerFactory, $binding->commandHandlerFactory);
    }

    public function testResolvingCommandHandlerThatIsClosureReturnsClosureCommandHandler(): void
    {
        $command = new Command('name', [], [], '', '');
        $invoked = false;
        // Note:  Can't use a short closure here because of $invoked using a reference
        $expectedCommandHandler = function (Input $input, IOutput $output) use (&$invoked) {
            $invoked = true;
        };
        $commandHandlerFactory = fn () => $expectedCommandHandler;
        $binding = new CommandBinding($command, $commandHandlerFactory);
        $actualCommandHandler = $binding->resolveCommandHandler();
        $this->assertInstanceOf(ClosureCommandHandler::class, $actualCommandHandler);
        $actualCommandHandler->handle(new Input('name', [], []), $this->createMock(IOutput::class));
        $this->assertTrue($invoked);
    }

    public function testResolvingCommandHandlerThatIsCommandHandlerReturnsTheCommandHandler(): void
    {
        $command = new Command('name', [], [], '', '');
        $expectedCommandHandler = $this->createMock(ICommandHandler::class);
        $commandHandlerFactory = fn () => $expectedCommandHandler;
        $binding = new CommandBinding($command, $commandHandlerFactory);
        $this->assertSame($expectedCommandHandler, $binding->resolveCommandHandler());
    }

    public function testResolvingCommandHandlerThatIsNotCommandHandlerThrowsInvalidArgumentException(): void
    {
        $command = new Command('name', [], [], '', '');
        $commandHandlerFactory = fn () => new stdClass();
        $binding = new CommandBinding($command, $commandHandlerFactory);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Command handler must implement ' . ICommandHandler::class . ' or be a closure');
        $binding->resolveCommandHandler();
    }
}
