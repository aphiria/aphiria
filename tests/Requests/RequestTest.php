<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Requests;

use Aphiria\Console\Requests\Request;
use PHPUnit\Framework\TestCase;

/**
 * Tests the console request
 */
class RequestTest extends TestCase
{
    public function testPropertiesAreSetInConstructor(): void
    {
        $expectedCommandName = 'foo';
        $expectedArgumentValues = ['arg'];
        $expectedOptions = ['opt' => 'val'];
        $request = new Request($expectedCommandName, $expectedArgumentValues, $expectedOptions);
        $this->assertSame($expectedCommandName, $request->commandName);
        $this->assertSame($expectedArgumentValues, $request->argumentValues);
        $this->assertSame($expectedOptions, $request->options);
    }
}
