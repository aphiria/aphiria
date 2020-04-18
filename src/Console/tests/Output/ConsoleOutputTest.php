<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output;

use Aphiria\Console\Output\ConsoleOutput;
use PHPUnit\Framework\TestCase;

class ConsoleOutputTest extends TestCase
{
    public function testClearWritesAsciiCodesToClearScreen(): void
    {
        $output = new class() extends ConsoleOutput {
            public string $message = '';

            public function write($messages): void
            {
                $this->message = $messages;
            }
        };
        $output->clear();
        $this->assertEquals(\chr(27) . '[2J' . \chr(27) . '[;H', $output->message);
    }
}
