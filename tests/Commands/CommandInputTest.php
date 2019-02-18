<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Commands;

use Aphiria\Console\Commands\CommandInput;
use PHPUnit\Framework\TestCase;

/**
 * Tests the command input
 */
class CommandInputTest extends TestCase
{
    public function testPropertiesAreSetInConstructor(): void
    {
        $expectedArguments = ['arg' => 'val'];
        $expectedOptions = ['opt' => 'val'];
        $commandInput = new CommandInput($expectedArguments, $expectedOptions);
        $this->assertSame($expectedArguments, $commandInput->arguments);
        $this->assertSame($expectedOptions, $commandInput->options);
    }
}
