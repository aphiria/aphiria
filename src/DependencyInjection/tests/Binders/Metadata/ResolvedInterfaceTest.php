<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Binders\Metadata;

use Aphiria\DependencyInjection\Binders\Metadata\ResolvedInterface;
use Aphiria\DependencyInjection\TargetedContext;
use Aphiria\DependencyInjection\UniversalContext;
use PHPUnit\Framework\TestCase;

class ResolvedInterfaceTest extends TestCase
{
    public function testGetInterfaceReturnsSetInterface(): void
    {
        $resolvedInterface = new class() {
        };
        $interface = new ResolvedInterface($resolvedInterface::class, new UniversalContext());
        $this->assertSame($resolvedInterface::class, $interface->getInterface());
    }

    public function testGetContextReturnsSetContext(): void
    {
        $target = new class() {
        };
        $resolvedInterface = new class() {
        };
        $expectedContext = new TargetedContext($target::class);
        $interface = new ResolvedInterface($resolvedInterface::class, $expectedContext);
        $this->assertSame($expectedContext, $interface->getContext());
    }
}
