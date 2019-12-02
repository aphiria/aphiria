<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ConsoleAnnotations\Tests;

use Aphiria\ConsoleAnnotations\DependencyResolutionException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the dependency resolution exception
 */
class DependencyResolutionExceptionTest extends TestCase
{
    public function testCommandHandlerClassNameIsSetFromConstructor(): void
    {
        $this->assertEquals('foo', (new DependencyResolutionException('foo'))->getCommandHandlerClassName());
    }
}
