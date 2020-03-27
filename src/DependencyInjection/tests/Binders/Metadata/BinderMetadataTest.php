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

use Aphiria\DependencyInjection\Binders\Metadata\BinderMetadata;
use Aphiria\DependencyInjection\Binders\Metadata\BoundInterface;
use Aphiria\DependencyInjection\Binders\Metadata\ResolvedInterface;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\Tests\Binders\Mocks\Binder;
use PHPUnit\Framework\TestCase;

/**
 * Tests the binder metadata
 */
class BinderMetadataTest extends TestCase
{
    public function testGetBinderReturnsSetBinder(): void
    {
        $expectedBinder = new class() extends Binder
        {
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
        $binder = new class() extends Binder
        {
            public function bind(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $expectedBoundInterfaces = [new BoundInterface('foo'), new BoundInterface('bar')];
        $binderMetadata = new BinderMetadata($binder, $expectedBoundInterfaces, []);
        $this->assertSame($expectedBoundInterfaces, $binderMetadata->getBoundInterfaces());
    }

    public function testGetBoundInterfacesReturnsSetResolvedInterfaces(): void
    {
        $binder = new class() extends Binder
        {
            public function bind(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $expectedResolvedInterfaces = [new ResolvedInterface('foo'), new ResolvedInterface('bar')];
        $binderMetadata = new BinderMetadata($binder, [], $expectedResolvedInterfaces);
        $this->assertSame($expectedResolvedInterfaces, $binderMetadata->getResolvedInterfaces());
    }
}
