<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Input\Compilers;

use Aphiria\Console\Input\Compilers\CommandNotFoundException;
use PHPUnit\Framework\TestCase;

class CommandNotFoundExceptionTest extends TestCase
{
    public function testGettingCommandNameReturnsOneSetInConstructor(): void
    {
        $exception = new CommandNotFoundException('foo');
        $this->assertSame('foo', $exception->commandName);
    }
}
