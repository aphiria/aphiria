<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Commands;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandBinding;
use PHPUnit\Framework\TestCase;

class CommandBindingTest extends TestCase
{
    public function testPropertiesAreSetInConstructorWhenUsingCommandHandlerInterface(): void
    {
        $expectedCommand = new Command('name', [], [], '', '');
        $binding = new CommandBinding($expectedCommand, 'Foo');
        $this->assertSame($expectedCommand, $binding->command);
        $this->assertEquals('Foo', $binding->commandHandlerClassName);
    }
}
