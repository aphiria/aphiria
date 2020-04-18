<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Input;

use Aphiria\Console\Input\Input;
use PHPUnit\Framework\TestCase;

class InputTest extends TestCase
{
    public function testPropertiesAreSetInConstructor(): void
    {
        $expectedCommandName = 'foo';
        $expectedArguments = ['arg' => 'val'];
        $expectedOptions = ['opt' => 'val'];
        $input = new Input($expectedCommandName, $expectedArguments, $expectedOptions);
        $this->assertSame($expectedCommandName, $input->commandName);
        $this->assertSame($expectedArguments, $input->arguments);
        $this->assertSame($expectedOptions, $input->options);
    }
}
