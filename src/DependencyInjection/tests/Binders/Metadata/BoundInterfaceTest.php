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

use Aphiria\DependencyInjection\Binders\Metadata\BoundInterface;
use PHPUnit\Framework\TestCase;

/**
 * Tests the bound interface
 */
class BoundInterfaceTest extends TestCase
{

    public function testGetInterfaceReturnsSetInterface(): void
    {
        $interface = new BoundInterface('foo');
        $this->assertEquals('foo', $interface->getInterface());
    }

    public function testGetTargetClassReturnsSetTargetClass(): void
    {
        $interfaceWithTarget = new BoundInterface('foo', 'bar');
        $this->assertEquals('bar', $interfaceWithTarget->getTargetClass());
        $interfaceWithoutTarget = new BoundInterface('foo');
        $this->assertNull($interfaceWithoutTarget->getTargetClass());
    }

    public function testIsTargetedReturnsWhetherOrNotTargetIsSet(): void
    {
        $interfaceWithTarget = new BoundInterface('foo', 'bar');
        $this->assertTrue($interfaceWithTarget->isTargeted());
        $interfaceWithoutTarget = new BoundInterface('foo');
        $this->assertFalse($interfaceWithoutTarget->isTargeted());
    }
}
