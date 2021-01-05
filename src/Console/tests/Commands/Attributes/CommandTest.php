<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Commands\Attributes;

use Aphiria\Console\Commands\Attributes\Command;
use PHPUnit\Framework\TestCase;

class CommandTest extends TestCase
{
    public function testAllPropertiesAreSetInConstructor(): void
    {
        $command = new Command('command', 'description', 'help');
        $this->assertSame('command', $command->name);
        $this->assertSame('description', $command->description);
        $this->assertSame('help', $command->helpText);
    }
}
