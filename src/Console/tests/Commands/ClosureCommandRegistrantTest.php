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

use Aphiria\Console\Commands\ClosureCommandRegistrant;
use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandBinding;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandHandler;
use PHPUnit\Framework\TestCase;

class ClosureCommandRegistrantTest extends TestCase
{
    public function testRegisteringCommandsRegistersCommandsFromClosures(): void
    {
        $expectedCommand = new Command('foo');
        $expectedCommandHandlerFactory = fn () => $this->createMock(ICommandHandler::class);
        $closures = [function (CommandRegistry $commands) use ($expectedCommand, $expectedCommandHandlerFactory) {
            $commands->registerCommand($expectedCommand, $expectedCommandHandlerFactory);
        }];
        $closureCommandRegistrant = new ClosureCommandRegistrant($closures);
        $commands = new CommandRegistry();
        $closureCommandRegistrant->registerCommands($commands);
        $this->assertCount(1, $commands->getAllCommands());
        /** @var CommandBinding $actualBinding */
        $actualBinding = null;
        $this->assertTrue($commands->tryGetBinding('foo', $actualBinding));
        $this->assertSame($expectedCommand, $actualBinding->command);
        $this->assertSame($expectedCommandHandlerFactory, $actualBinding->commandHandlerFactory);
    }
}
