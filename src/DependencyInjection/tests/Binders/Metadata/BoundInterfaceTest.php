<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Binders\Metadata;

use Aphiria\DependencyInjection\Binders\Metadata\BoundInterface;
use Aphiria\DependencyInjection\TargetedContext;
use Aphiria\DependencyInjection\UniversalContext;
use PHPUnit\Framework\TestCase;

class BoundInterfaceTest extends TestCase
{
    public function testGetInterfaceReturnsSetInterface(): void
    {
        $interface = new BoundInterface(self::class, new UniversalContext());
        $this->assertSame(self::class, $interface->interface);
    }

    public function testGetContextReturnsSetContext(): void
    {
        $target = new class () {
        };
        $expectedContext = new TargetedContext($target::class);
        $interface = new BoundInterface(self::class, $expectedContext);
        $this->assertSame($expectedContext, $interface->context);
    }
}
