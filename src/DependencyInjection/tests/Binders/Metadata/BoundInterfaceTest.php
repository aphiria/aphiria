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
use Aphiria\DependencyInjection\TargetedContext;
use Aphiria\DependencyInjection\UniversalContext;
use PHPUnit\Framework\TestCase;

/**
 * Tests the bound interface
 */
class BoundInterfaceTest extends TestCase
{
    public function testGetInterfaceReturnsSetInterface(): void
    {
        $interface = new BoundInterface('foo', new UniversalContext());
        $this->assertEquals('foo', $interface->getInterface());
    }

    public function testGetContextReturnsSetContext(): void
    {
        $expectedContext = new TargetedContext('bar');
        $interface = new BoundInterface('foo', $expectedContext);
        $this->assertSame($expectedContext, $interface->getContext());
    }
}
