<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Commands;

use Aphiria\Console\Commands\ClosureCommandHandler;
use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandBinding;
use Aphiria\Console\Commands\CommandInput;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Output\IOutput;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the command binding
 */
class CommandBindingTest extends TestCase
{
    public function testClosureIsWrappedInClosureCommandHandler(): void
    {
        $closure = function (CommandInput $input, IOutput $output) {
            // Don't do anything
        };
        $expectedCommand = new Command('foo', [], [], '');
        $binding = new CommandBinding($expectedCommand, $closure);
        $this->assertInstanceOf(ClosureCommandHandler::class, $binding->commandHandler);
        $this->assertSame($expectedCommand, $binding->command);
    }

    public function testExceptionThrownWhenNotUsingClosureNorCommandHandler(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CommandBinding(new Command('name', [], [], '', ''), 'foo');
    }

    public function testPropertiesAreSetInConstructorWhenUsingCommandHandlerInterface(): void
    {
        $expectedCommand = new Command('name', [], [], '', '');
        /** @var ICommandHandler|MockObject $expectedCommandHandler */
        $expectedCommandHandler = $this->createMock(ICommandHandler::class);
        $binding = new CommandBinding($expectedCommand, $expectedCommandHandler);
        $this->assertSame($expectedCommand, $binding->command);
        $this->assertSame($expectedCommandHandler, $binding->commandHandler);
    }
}
