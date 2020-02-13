<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests;

use Aphiria\DependencyInjection\ResolutionException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the resolution exception
 */
class ResolutionExceptionTest extends TestCase
{
    public function testGetInterfaceReturnsInterfaceInjectedInConstructor(): void
    {
        $exception = new ResolutionException('foo', null);
        $this->assertEquals('foo', $exception->getInterface());
    }

    public function testGetTargetClassReturnsTargetClassInjectedInConstructor(): void
    {
        $exception = new ResolutionException('foo', 'bar');
        $this->assertEquals('bar', $exception->getTargetClass());
    }
}
