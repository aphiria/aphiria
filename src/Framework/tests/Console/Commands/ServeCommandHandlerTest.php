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

use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;
use Aphiria\Framework\Console\Commands\ServeCommandHandler;
use PHPUnit\Framework\TestCase;

class ServeCommandHandlerTest extends TestCase
{
    public function testHandlingRunCorrectPhpCommandAndWritesCorrectOutput(): void
    {
        $output = $this->createMock(IOutput::class);
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
