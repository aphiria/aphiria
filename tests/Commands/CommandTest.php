<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Commands;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Requests\Argument;
use Aphiria\Console\Requests\ArgumentTypes;
use Aphiria\Console\Requests\Option;
use Aphiria\Console\Requests\OptionTypes;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the command
 */
class CommandTest extends TestCase
{
    public function testEmptyNameThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Command('', [], [], '', '');
    }

    public function testPropertiesAreSetInConstructor(): void
    {
        $expectedName = 'foo';
        $expectedArguments = [new Argument('arg', ArgumentTypes::REQUIRED, 'description')];
        $expectedOptions = [new Option('opt', 'o', OptionTypes::REQUIRED_VALUE, 'description')];
        $expectedDescription = 'description';
        $expectedHelpText = 'help';
        $command = new Command(
            $expectedName,
            $expectedArguments,
            $expectedOptions,
            $expectedDescription,
            $expectedHelpText
        );
        $this->assertSame($expectedName, $command->name);
        $this->assertSame($expectedArguments, $command->arguments);
        $this->assertSame($expectedOptions, $command->options);
        $this->assertSame($expectedDescription, $command->description);
        $this->assertSame($expectedHelpText, $command->helpText);
    }
}
