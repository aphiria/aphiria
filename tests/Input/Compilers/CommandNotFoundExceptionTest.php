<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Input\Compilers;

use Aphiria\Console\Input\Compilers\CommandNotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the command not found exception
 */
class CommandNotFoundExceptionTest extends TestCase
{
    public function testGettingCommandNameReturnsOneSetInConstructor(): void
    {
        $exception = new CommandNotFoundException('foo');
        $this->assertEquals('foo', $exception->getCommandName());
    }
}
