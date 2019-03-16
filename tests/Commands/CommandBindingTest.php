<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Commands;

use Aphiria\Console\Commands\ClosureCommandHandler;
use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandBinding;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;
use PHPUnit\Framework\TestCase;

/**
 * Tests the command binding
 */
class CommandBindingTest extends TestCase
{
    public function testPropertiesAreSetInConstructorWhenUsingCommandHandlerInterface(): void
    {
        $expectedCommand = new Command('name', [], [], '', '');
        $expectedCommandHandlerFactory = function () {
            return $this->createMock(ICommandHandler::class);
        };
        $binding = new CommandBinding($expectedCommand, $expectedCommandHandlerFactory);
        $this->assertSame($expectedCommand, $binding->command);
        $this->assertSame($expectedCommandHandlerFactory, $binding->commandHandlerFactory);
    }

    public function testResolvingCommandHandlerThatIsClosureReturnsClosureCommandHandler(): void
    {
        $command = new Command('name', [], [], '', '');
        $invoked = false;
        $expectedCommandHandler = function (Input $input, IOutput $output) use (&$invoked) {
            $invoked = true;
        };
        $commandHandlerFactory = function () use ($expectedCommandHandler) {
            return $expectedCommandHandler;
        };
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
        $commandHandlerFactory = function () use ($expectedCommandHandler) {
            return $expectedCommandHandler;
        };
        $binding = new CommandBinding($command, $commandHandlerFactory);
        $this->assertSame($expectedCommandHandler, $binding->resolveCommandHandler());
    }
}
