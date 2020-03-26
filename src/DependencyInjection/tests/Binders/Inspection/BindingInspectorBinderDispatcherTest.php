<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Binders\Inspection;

use Closure;
use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\Binders\Inspection\BindingInspectorBinderDispatcher;
use Aphiria\DependencyInjection\Binders\Inspection\BinderBinding;
use Aphiria\DependencyInjection\Binders\Inspection\Caching\IBinderBindingCache;
use Aphiria\DependencyInjection\Binders\Inspection\UniversalBinderBinding;
use Aphiria\DependencyInjection\IContainer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the inspection binding binder dispatcher
 */
class BindingInspectorBinderDispatcherTest extends TestCase
{
    private BindingInspectorBinderDispatcher $dispatcher;
    /** @var IContainer|MockObject */
    private IContainer $container;
    /** @var IBinderBindingCache|MockObject */
    private IBinderBindingCache $cache;

    protected function setUp(): void
    {
        $this->container = $this->createMock(IContainer::class);
        $this->cache = $this->createMock(IBinderBindingCache::class);
        $this->dispatcher = new BindingInspectorBinderDispatcher($this->cache);
    }

    public function testDispatchingWithCacheForcesBindingInspectionAndSetsCacheOnCacheMiss(): void
    {
        $expectedBinder = new class extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindPrototype('foo', 'bar');
            }
        };
        $this->cache->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $this->cache->expects($this->once())
            ->method('set')
            ->with($this->callback(function (array $binderBindings) use ($expectedBinder) {
                /** @var BinderBinding[] $binderBindings */
                return \count($binderBindings) === 1
                    && $binderBindings[0]->getBinder() === $expectedBinder
                    && $binderBindings[0]->getInterface() === 'foo';
            }));
        $this->container->expects($this->once())
            ->method('bindFactory')
            ->with('foo', $this->callback(function (Closure $factory) {
                return true;
            }));
        $this->dispatcher->dispatch([$expectedBinder], $this->container);
    }

    public function testDispatchingWithCacheUsesResultsOnCacheHit(): void
    {
        $expectedBinder = new class extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindPrototype('foo', 'bar');
            }
        };
        $expectedBindings = [new UniversalBinderBinding('foo', $expectedBinder)];
        $this->cache->expects($this->once())
            ->method('get')
            ->willReturn($expectedBindings);
        $this->container->expects($this->once())
            ->method('bindFactory')
            ->with('foo', $this->callback(fn (Closure $factory)  => true));
        $this->dispatcher->dispatch([$expectedBinder], $this->container);
    }

    public function testDispatchingWithNoCacheForcesBindingInspection(): void
    {
        $dispatcher = new BindingInspectorBinderDispatcher();
        $expectedBinder = new class extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindPrototype('foo', 'bar');
            }
        };
        $this->container->expects($this->once())
            ->method('bindFactory')
            ->with('foo', $this->callback(fn (Closure $factory) => true));
        $dispatcher->dispatch([$expectedBinder], $this->container);
    }
}
