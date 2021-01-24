<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Binders\Metadata;

use Aphiria\DependencyInjection\Binders\Metadata\BinderMetadata;
use Aphiria\DependencyInjection\Binders\Metadata\BoundInterface;
use Aphiria\DependencyInjection\Binders\Metadata\ResolvedInterface;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\Tests\Binders\Mocks\Binder;
use Aphiria\DependencyInjection\UniversalContext;
use PHPUnit\Framework\TestCase;

class BinderMetadataTest extends TestCase
{
    public function testGetBinderReturnsSetBinder(): void
    {
        $expectedBinder = new class() extends Binder {
            public function bind(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $binderMetadata = new BinderMetadata($expectedBinder, [], []);
        $this->assertSame($expectedBinder, $binderMetadata->getBinder());
    }

    public function testGetBoundInterfacesReturnsSetBoundInterfaces(): void
    {
        $binder = new class() extends Binder {
            public function bind(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $boundInterface1 = new class() {
        };
        $boundInterface2 = new class() {
        };
        $expectedBoundInterfaces = [
            new BoundInterface($boundInterface1::class, new UniversalContext()),
            new BoundInterface($boundInterface2::class, new UniversalContext())
        ];
        $binderMetadata = new BinderMetadata($binder, $expectedBoundInterfaces, []);
        $this->assertSame($expectedBoundInterfaces, $binderMetadata->getBoundInterfaces());
    }

    public function testGetBoundInterfacesReturnsSetResolvedInterfaces(): void
    {
        $binder = new class() extends Binder {
            public function bind(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $resolvedInterface1 = new class() {
        };
        $resolvedInterface2 = new class() {
        };
        $expectedResolvedInterfaces = [
            new ResolvedInterface($resolvedInterface1::class, new UniversalContext()),
            new ResolvedInterface($resolvedInterface2::class, new UniversalContext())
        ];
        $binderMetadata = new BinderMetadata($binder, [], $expectedResolvedInterfaces);
        $this->assertSame($expectedResolvedInterfaces, $binderMetadata->getResolvedInterfaces());
    }
}
