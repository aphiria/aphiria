<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Binders;

use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\Binders\LazyBinderDispatcher;
use Aphiria\DependencyInjection\Binders\Metadata\BinderMetadata;
use Aphiria\DependencyInjection\Binders\Metadata\BinderMetadataCollection;
use Aphiria\DependencyInjection\Binders\Metadata\ContainerBinderMetadataCollector;
use Aphiria\DependencyInjection\Binders\Metadata\BoundInterface;
use Aphiria\DependencyInjection\Binders\Metadata\Caching\IBinderMetadataCollectionCache;
use Aphiria\DependencyInjection\ClassContainerBinding;
use Aphiria\DependencyInjection\Container;
use Aphiria\DependencyInjection\FactoryContainerBinding;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\IContainerBinding;
use Aphiria\DependencyInjection\InstanceContainerBinding;
use Aphiria\DependencyInjection\Tests\Binders\Metadata\Mocks\Foo;
use Aphiria\DependencyInjection\Tests\Binders\Metadata\Mocks\IFoo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the lazy binder dispatcher
 */
class LazyBinderDispatcherTest extends TestCase
{
    /** @var IContainer|MockObject */
    private IContainer $container;

    protected function setUp(): void
    {
        // Allow us to more easily retrieve bindings for testing purposes
        $this->container = new class extends Container
        {
            public function getBinding(string $interface): ?IContainerBinding
            {
                return parent::getBinding($interface);
            }
        };
    }

    public function testDispatchingBinderThatResolvesAnotherBindersBindingCausesBothToBeActivelyDispatched(): void
    {
        $binderA = new  class extends Binder {
            public bool $binderDispatched = false;

            public function bind(IContainer $container): void
            {
                if (!$container instanceof ContainerBinderMetadataCollector) {
                    // This is a hacky workaround to check if this was dispatched by a real container, not just the metadata collectors
                    $this->binderDispatched = true;
                }

                $container->bindInstance(IFoo::class, new Foo());
            }
        };
        $binderB = new class extends Binder {
            public bool $binderDispatched = false;

            public function bind(IContainer $container): void
            {
                if (!$container instanceof ContainerBinderMetadataCollector) {
                    // This is a hacky workaround to check if this was dispatched by a real container, not just the metadata collectors
                    $this->binderDispatched = true;
                }

                $container->resolve(IFoo::class);
            }
        };
        $this->createDispatcher()->dispatch([$binderA, $binderB], $this->container);
        $this->assertFalse($binderA->binderDispatched);
        $this->assertFalse($binderB->binderDispatched);
        $this->container->resolve(IFoo::class);
        $this->assertTrue($binderA->binderDispatched);
        $this->assertTrue($binderB->binderDispatched);
    }

    public function testDispatchingTargetedBindingRegistersBindingsFromBinder(): void
    {
        $binder = new class extends Binder {
            public function bind(IContainer $container): void
            {
                $container->for('bar', function (IContainer $container) {
                    $container->bindInstance(IFoo::class, new Foo());
                });
            }
        };
        $this->createDispatcher()->dispatch([$binder], $this->container);
        /** @var FactoryContainerBinding $lazyBinding */
        $lazyBinding = $this->container->for('bar', fn (IContainer $container) => $container->getBinding(IFoo::class));
        $this->assertInstanceOf(FactoryContainerBinding::class, $lazyBinding);
        $this->assertInstanceOf(Foo::class, ($lazyBinding->getFactory())());
        /** @var InstanceContainerBinding $bindingFromBinder */
        $bindingFromBinder = $this->container->for('bar', fn (IContainer $container) => $container->getBinding(IFoo::class));
        $this->assertInstanceOf(InstanceContainerBinding::class, $bindingFromBinder);
        $this->assertInstanceOf(Foo::class, $bindingFromBinder->getInstance());
    }

    public function testDispatchingUniversalBindingRegistersBindingsFromBinder(): void
    {
        $binder = new class extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindInstance(IFoo::class, new Foo());
            }
        };
        $this->createDispatcher()->dispatch([$binder], $this->container);
        /** @var FactoryContainerBinding $lazyBinding */
        $lazyBinding = $this->container->getBinding(IFoo::class);
        $this->assertInstanceOf(FactoryContainerBinding::class, $lazyBinding);
        $this->assertInstanceOf(Foo::class, ($lazyBinding->getFactory())());
        /** @var InstanceContainerBinding $bindingFromBinder */
        $bindingFromBinder = $this->container->getBinding(IFoo::class);
        $this->assertInstanceOf(InstanceContainerBinding::class, $bindingFromBinder);
        $this->assertInstanceOf(Foo::class, $bindingFromBinder->getInstance());
    }

    public function testDispatchingWithCacheForcesCreationOfMetadataCollectionAndSetsCacheOnCacheMiss(): void
    {
        $binder = new class extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindPrototype('foo', 'bar');
            }
        };
        $cache = $this->createMock(IBinderMetadataCollectionCache::class);
        $cache->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $cache->expects($this->once())
            ->method('set')
            ->with($this->callback(function (BinderMetadataCollection $collection) use ($binder) {
                $expectedCollection = new BinderMetadataCollection([
                    new BinderMetadata($binder, [new BoundInterface('foo')], [])
                ]);

                // Intentionally not checking reference equality
                return $collection == $expectedCollection;
            }));
        $this->createDispatcher($cache)->dispatch([$binder], $this->container);
    }

    public function testDispatchingWithCacheUsesResultsOnCacheHit(): void
    {
        $binder = new class extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindPrototype(IFoo::class, Foo::class);
            }
        };
        $this->createDispatcher()->dispatch([$binder], $this->container);
        /** @var FactoryContainerBinding $lazyBinding */
        $lazyBinding = $this->container->getBinding(IFoo::class);
        $this->assertInstanceOf(FactoryContainerBinding::class, $lazyBinding);
        $this->assertInstanceOf(Foo::class, ($lazyBinding->getFactory())());
        /** @var ClassContainerBinding $bindingFromBinder */
        $bindingFromBinder = $this->container->getBinding(IFoo::class);
        $this->assertInstanceOf(ClassContainerBinding::class, $bindingFromBinder);
        $this->assertEquals(Foo::class, $bindingFromBinder->getConcreteClass());
    }

    public function testDispatchingWithNoCacheForcesCollectionCreation(): void
    {
        $binder = new class extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindPrototype(IFoo::class, Foo::class);
            }
        };
        $this->createDispatcher()->dispatch([$binder], $this->container);
        /** @var FactoryContainerBinding $lazyBinding */
        $lazyBinding = $this->container->getBinding(IFoo::class);
        $this->assertInstanceOf(FactoryContainerBinding::class, $lazyBinding);
        $this->assertInstanceOf(Foo::class, ($lazyBinding->getFactory())());
        /** @var ClassContainerBinding $bindingFromBinder */
        $bindingFromBinder = $this->container->getBinding(IFoo::class);
        $this->assertInstanceOf(ClassContainerBinding::class, $bindingFromBinder);
        $this->assertEquals(Foo::class, $bindingFromBinder->getConcreteClass());
    }

    /**
     * Creates a lazy binder dispatcher to use in tests
     *
     * @param IBinderMetadataCollectionCache|null $collectionCache The collection cache if we're using one, otherwise null
     * @return LazyBinderDispatcher The dispatcher
     */
    private function createDispatcher(IBinderMetadataCollectionCache $collectionCache = null): LazyBinderDispatcher
    {
        return new LazyBinderDispatcher($collectionCache);
    }
}
