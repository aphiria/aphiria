<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Commands;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandBinding;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;
use PHPUnit\Framework\TestCase;

class CommandBindingTest extends TestCase
{
    public function testPropertiesAreSetInConstructorWhenUsingCommandHandlerInterface(): void
    {
        $expectedCommand = new Command('name', [], [], '', '');
        $commandHandler = new class() implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $binding = new CommandBinding($expectedCommand, $commandHandler::class);
        $this->assertSame($expectedCommand, $binding->command);
        $this->assertSame($commandHandler::class, $binding->commandHandlerClassName);
    }
}
