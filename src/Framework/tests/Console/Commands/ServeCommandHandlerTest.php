<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Console\Commands;

use Aphiria\Console\Drivers\IDriver;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;
use Aphiria\Framework\Console\Commands\ServeCommandHandler;
use PHPUnit\Framework\MockObject\Runtime\PropertyHook;
use PHPUnit\Framework\TestCase;

class ServeCommandHandlerTest extends TestCase
{
    public function testHandlingRunCorrectPhpCommandAndWritesCorrectOutput(): void
    {
        $output = $this->createMock(IOutput::class);
        $driver = new class () implements IDriver {
            public int $cliWidth = 3;
            public int $cliHeight = 2;

            public function readHiddenInput(IOutput $output): ?string
            {
                return null;
            }
        };
        $output->method(PropertyHook:get('driver'))
            ->willReturn($driver);
        $output->expects($this->once())
            ->method('writeln')
            ->with('<info>Running at http://localhost.app:443</info>');
        $input = new Input(
            'app:serve',
            [],
            [
                'domain' => 'localhost.app',
                'port' => 443,
                'router' => 'router',
                'docroot' => 'public'
            ]
        );
        $handler = new class () extends ServeCommandHandler {
            public ?string $ranCommand = null;

            protected function runPhpCommand(string $command): void
            {
                $this->ranCommand = $command;
            }
        };
        $handler->handle($input, $output);
        $this->assertSame('"' . PHP_BINARY . '" -S localhost.app:443 -t "public" "router"', $handler->ranCommand);
    }
}
