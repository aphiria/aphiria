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
use Aphiria\Console\Commands\CommandHandlerBinding;
use Aphiria\Console\Commands\CommandInput;
use Aphiria\Console\Commands\ICommandHandler;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the command handler binding
 */
class CommandHandlerBindingTest extends TestCase
{
    public function testExceptionThrownWhenNotUsingClosureNorCommandHandler(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CommandHandlerBinding(new Command('name', [], [], '', ''), 'foo');
    }

    public function testPropertiesAreSetInConstructorWhenUsingClosure(): void
    {
        $expectedCommand = new Command('name', [], [], '', '');
        $expectedCommandHandler = function (CommandInput $commandInput) {
            return;
        };
        $binding = new CommandHandlerBinding($expectedCommand, $expectedCommandHandler);
        $this->assertSame($expectedCommand, $binding->command);
        $this->assertSame($expectedCommandHandler, $binding->commandHandler);
    }

    public function testPropertiesAreSetInConstructorWhenUsingCommandHandlerInterface(): void
    {
        $expectedCommand = new Command('name', [], [], '', '');
        /** @var ICommandHandler|MockObject $expectedCommandHandler */
        $expectedCommandHandler = $this->createMock(ICommandHandler::class);
        $binding = new CommandHandlerBinding($expectedCommand, $expectedCommandHandler);
        $this->assertSame($expectedCommand, $binding->command);
        $this->assertSame($expectedCommandHandler, $binding->commandHandler);
    }
}
