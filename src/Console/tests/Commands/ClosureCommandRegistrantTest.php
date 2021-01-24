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

use Aphiria\Console\Commands\ClosureCommandRegistrant;
use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandBinding;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;
use PHPUnit\Framework\TestCase;

class ClosureCommandRegistrantTest extends TestCase
{
    public function testRegisteringCommandsRegistersCommandsFromClosures(): void
    {
        $expectedCommand = new Command('foo');
        $commandHandler = new class() implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $closures = [function (CommandRegistry $commands) use ($expectedCommand, $commandHandler): void {
            $commands->registerCommand($expectedCommand, $commandHandler::class);
        }];
        $closureCommandRegistrant = new ClosureCommandRegistrant($closures);
        $commands = new CommandRegistry();
        $closureCommandRegistrant->registerCommands($commands);
        $this->assertCount(1, $commands->getAllCommands());
        /** @var CommandBinding $actualBinding */
        $actualBinding = null;
        $this->assertTrue($commands->tryGetBinding('foo', $actualBinding));
        $this->assertSame($expectedCommand, $actualBinding->command);
        $this->assertSame($commandHandler::class, $actualBinding->commandHandlerClassName);
    }
}
