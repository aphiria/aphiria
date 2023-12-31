<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output;

use Aphiria\Console\Output\ConsoleOutput;
use PHPUnit\Framework\TestCase;

class ConsoleOutputTest extends TestCase
{
    public function testClearWritesAsciiCodesToClearScreen(): void
    {
        $output = new class () extends ConsoleOutput {
            public string|array $message = '';

            public function write(string|array $messages): void
            {
                $this->message = $messages;
            }
        };
        $output->clear();
        $this->assertSame(\chr(27) . '[2J' . \chr(27) . '[;H', $output->message);
    }
}
