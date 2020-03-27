<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Binders\Metadata;

use Aphiria\DependencyInjection\Binders\Metadata\ResolvedInterface;
use PHPUnit\Framework\TestCase;

/**
 * Tests the resolved interface
 */
class ResolvedInterfaceTest extends TestCase
{
    public function testGetInterfaceReturnsSetInterface(): void
    {
        $interface = new ResolvedInterface('foo');
        $this->assertEquals('foo', $interface->getInterface());
    }

    public function testGetTargetClassReturnsSetTargetClass(): void
    {
        $interfaceWithTarget = new ResolvedInterface('foo', 'bar');
        $this->assertEquals('bar', $interfaceWithTarget->getTargetClass());
        $interfaceWithoutTarget = new ResolvedInterface('foo');
        $this->assertNull($interfaceWithoutTarget->getTargetClass());
    }

    public function testIsTargetedReturnsWhetherOrNotTargetIsSet(): void
    {
        $interfaceWithTarget = new ResolvedInterface('foo', 'bar');
        $this->assertTrue($interfaceWithTarget->isTargeted());
        $interfaceWithoutTarget = new ResolvedInterface('foo');
        $this->assertFalse($interfaceWithoutTarget->isTargeted());
    }
}
