<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Input;

use Aphiria\Console\Input\Input;
use PHPUnit\Framework\TestCase;

/**
 * Tests the console input
 */
class InputTest extends TestCase
{
    public function testPropertiesAreSetInConstructor(): void
    {
        $expectedCommandName = 'foo';
        $expectedArgumentValues = ['arg'];
        $expectedOptions = ['opt' => 'val'];
        $input = new Input($expectedCommandName, $expectedArgumentValues, $expectedOptions);
        $this->assertSame($expectedCommandName, $input->commandName);
        $this->assertSame($expectedArgumentValues, $input->argumentValues);
        $this->assertSame($expectedOptions, $input->options);
    }
}
