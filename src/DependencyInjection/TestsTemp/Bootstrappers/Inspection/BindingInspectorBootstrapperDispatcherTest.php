<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/dependency-injection/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Bootstrappers\Inspection;

use Closure;
use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper;
use Aphiria\DependencyInjection\Bootstrappers\Inspection\BindingInspectorBootstrapperDispatcher;
use Aphiria\DependencyInjection\Bootstrappers\Inspection\BootstrapperBinding;
use Aphiria\DependencyInjection\Bootstrappers\Inspection\Caching\IBootstrapperBindingCache;
use Aphiria\DependencyInjection\Bootstrappers\Inspection\UniversalBootstrapperBinding;
use Aphiria\DependencyInjection\IContainer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the inspection binding bootstrapper dispatcher
 */
class BindingInspectorBootstrapperDispatcherTest extends TestCase
{
    private BindingInspectorBootstrapperDispatcher $dispatcher;
    /** @var IContainer|MockObject */
    private IContainer $container;
    /** @var IBootstrapperBindingCache|MockObject */
    private IBootstrapperBindingCache $cache;

    protected function setUp(): void
    {
        $this->container = $this->createMock(IContainer::class);
        $this->cache = $this->createMock(IBootstrapperBindingCache::class);
        $this->dispatcher = new BindingInspectorBootstrapperDispatcher($this->container, $this->cache);
    }

    public function testDispatchingWithCacheForcesBindingInspectionAndSetsCacheOnCacheMiss(): void
    {
        $expectedBootstrapper = new class extends Bootstrapper {
            public function registerBindings(IContainer $container): void
            {
                $container->bindPrototype('foo', 'bar');
            }
        };
        $this->cache->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $this->cache->expects($this->once())
            ->method('set')
            ->with($this->callback(function (array $bootstrapperBindings) use ($expectedBootstrapper) {
                /** @var BootstrapperBinding[] $bootstrapperBindings */
                return \count($bootstrapperBindings) === 1
                    && $bootstrapperBindings[0]->getBootstrapper() === $expectedBootstrapper
                    && $bootstrapperBindings[0]->getInterface() === 'foo';
            }));
        $this->container->expects($this->once())
            ->method('bindFactory')
            ->with('foo', $this->callback(function (Closure $factory) {
                return true;
            }));
        $this->dispatcher->dispatch([$expectedBootstrapper]);
    }

    public function testDispatchingWithCacheUsesResultsOnCacheHit(): void
    {
        $expectedBootstrapper = new class extends Bootstrapper {
            public function registerBindings(IContainer $container): void
            {
                $container->bindPrototype('foo', 'bar');
            }
        };
        $expectedBindings = [new UniversalBootstrapperBinding('foo', $expectedBootstrapper)];
        $this->cache->expects($this->once())
            ->method('get')
            ->willReturn($expectedBindings);
        $this->container->expects($this->once())
            ->method('bindFactory')
            ->with('foo', $this->callback(fn (Closure $factory)  => true));
        $this->dispatcher->dispatch([$expectedBootstrapper]);
    }

    public function testDispatchingWithNoCacheForcesBindingInspection(): void
    {
        $dispatcher = new BindingInspectorBootstrapperDispatcher($this->container);
        $expectedBootstrapper = new class extends Bootstrapper {
            public function registerBindings(IContainer $container): void
            {
                $container->bindPrototype('foo', 'bar');
            }
        };
        $this->container->expects($this->once())
            ->method('bindFactory')
            ->with('foo', $this->callback(fn (Closure $factory) => true));
        $dispatcher->dispatch([$expectedBootstrapper]);
    }
}
