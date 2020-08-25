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
        $interface = new ResolvedInterface('foo', new UniversalContext());
        $this->assertSame('foo', $interface->getInterface());
    }

    public function testGetContextReturnsSetContext(): void
    {
        $expectedContext = new TargetedContext('bar');
        $interface = new ResolvedInterface('foo', $expectedContext);
        $this->assertSame($expectedContext, $interface->getContext());
    }
}
