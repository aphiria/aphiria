<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Binders;

use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\Binders\LazyBinderDispatcher;
use Aphiria\DependencyInjection\Binders\Metadata\BinderMetadata;
use Aphiria\DependencyInjection\Binders\Metadata\BinderMetadataCollection;
use Aphiria\DependencyInjection\Binders\Metadata\BoundInterface;
use Aphiria\DependencyInjection\Binders\Metadata\Caching\IBinderMetadataCollectionCache;
use Aphiria\DependencyInjection\Binders\Metadata\ContainerBinderMetadataCollector;
use Aphiria\DependencyInjection\ClassContainerBinding;
use Aphiria\DependencyInjection\Container;
use Aphiria\DependencyInjection\FactoryContainerBinding;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\IContainerBinding;
use Aphiria\DependencyInjection\InstanceContainerBinding;
use Aphiria\DependencyInjection\TargetedContext;
use Aphiria\DependencyInjection\Tests\Binders\Metadata\Mocks\Foo;
use Aphiria\DependencyInjection\Tests\Binders\Metadata\Mocks\IFoo;
use Aphiria\DependencyInjection\UniversalContext;
use PHPUnit\Framework\TestCase;

class LazyBinderDispatcherTest extends TestCase
{
    private IContainer $container;

    protected function setUp(): void
    {
        // Allow us to more easily retrieve bindings for testing purposes
        $this->container = new class () extends Container {
            /**
             * @template T of object
             * @param class-string<T> $interface The interface whose binding we want
             * @return IContainerBinding<T>|null The binding if one exists, otherwise null
             */
            public function getBinding(string $interface): ?IContainerBinding
            {
                return parent::getBinding($interface);
            }
        };
    }

    public function testDispatchingBinderThatResolvesAnotherBindersBindingCausesBothToBeActivelyDispatched(): void
    {
        $binderA = new class () extends Binder {
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
        $binderB = new class () extends Binder {
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
        /** @psalm-suppress DocblockTypeContradiction Psalm is incorrectly flagging this as always being false due to the assertion above */
        $this->assertTrue($binderA->binderDispatched);
        /** @psalm-suppress DocblockTypeContradiction Psalm is incorrectly flagging this as always being false due to the assertion above */
        $this->assertTrue($binderB->binderDispatched);
    }

    public function testDispatchingTargetedBindingRegistersBindingsFromBinder(): void
    {
        $target = new class () {
        };
        $binder = new class () extends Binder {
            public object $target;

            public function bind(IContainer $container): void
            {
                $container->for(new TargetedContext($this->target::class), function (IContainer $container) {
                    $container->bindInstance(IFoo::class, new Foo());
                });
            }
        };
        $binder->target = $target;
        $this->createDispatcher()->dispatch([$binder], $this->container);
        /** @var FactoryContainerBinding $lazyBinding */
        $lazyBinding = $this->container->for(new TargetedContext($target::class), fn (IContainer $container): mixed => $container->getBinding(IFoo::class));
        $this->assertInstanceOf(FactoryContainerBinding::class, $lazyBinding);
        $this->assertInstanceOf(Foo::class, ($lazyBinding->factory)());
        /** @var InstanceContainerBinding $bindingFromBinder */
        $bindingFromBinder = $this->container->for(new TargetedContext($target::class), fn (IContainer $container): mixed => $container->getBinding(IFoo::class));
        $this->assertInstanceOf(InstanceContainerBinding::class, $bindingFromBinder);
        $this->assertInstanceOf(Foo::class, $bindingFromBinder->instance);
    }

    public function testDispatchingUniversalBindingRegistersBindingsFromBinder(): void
    {
        $binder = new class () extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindInstance(IFoo::class, new Foo());
            }
        };
        $this->createDispatcher()->dispatch([$binder], $this->container);
        /** @var FactoryContainerBinding $lazyBinding */
        $lazyBinding = $this->container->getBinding(IFoo::class);
        $this->assertInstanceOf(FactoryContainerBinding::class, $lazyBinding);
        $this->assertInstanceOf(Foo::class, ($lazyBinding->factory)());
        /** @var InstanceContainerBinding $bindingFromBinder */
        $bindingFromBinder = $this->container->getBinding(IFoo::class);
        $this->assertInstanceOf(InstanceContainerBinding::class, $bindingFromBinder);
        $this->assertInstanceOf(Foo::class, $bindingFromBinder->instance);
    }

    public function testDispatchingUsesUniversalContextWhenBindingBinders(): void
    {
        $binder = new class () extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindInstance(IFoo::class, new Foo());
            }
        };
        $this->createDispatcher()->dispatch([$binder], $this->container);

        /**
         * We're testing that, when a lazy factory binding is invoked and the binder run, the binder's bindings occur
         * in the universal context, not in the targeted context that invoked the lazy factory binding.
         */
        $target = new class () {
        };
        $this->container->for(new TargetedContext($target::class), function (IContainer $container) {
            $container->resolve(IFoo::class);
        });
        $this->assertInstanceOf(Foo::class, $this->container->resolve(IFoo::class));
    }

    public function testDispatchingWithCacheForcesCreationOfMetadataCollectionAndSetsCacheOnCacheMiss(): void
    {
        $binder = new class () extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindClass(IFoo::class, Foo::class);
            }
        };
        $cache = $this->createMock(IBinderMetadataCollectionCache::class);
        $cache->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $cache->expects($this->once())
            ->method('set')
            ->with($this->callback(function (BinderMetadataCollection $collection) use ($binder): bool {
                $expectedCollection = new BinderMetadataCollection([
                    new BinderMetadata($binder, [new BoundInterface(IFoo::class, new UniversalContext())], [])
                ]);

                // Intentionally not checking reference equality
                return $collection == $expectedCollection;
            }));
        $this->createDispatcher($cache)->dispatch([$binder], $this->container);
    }

    public function testDispatchingWithCacheUsesResultsOnCacheHit(): void
    {
        $binder = new class () extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindClass(IFoo::class, Foo::class);
            }
        };
        $this->createDispatcher()->dispatch([$binder], $this->container);
        /** @var FactoryContainerBinding $lazyBinding */
        $lazyBinding = $this->container->getBinding(IFoo::class);
        $this->assertInstanceOf(FactoryContainerBinding::class, $lazyBinding);
        $this->assertInstanceOf(Foo::class, ($lazyBinding->factory)());
        /** @var ClassContainerBinding $bindingFromBinder */
        $bindingFromBinder = $this->container->getBinding(IFoo::class);
        $this->assertInstanceOf(ClassContainerBinding::class, $bindingFromBinder);
        $this->assertSame(Foo::class, $bindingFromBinder->concreteClass);
    }

    public function testDispatchingWithNoCacheForcesCollectionCreation(): void
    {
        $binder = new class () extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindClass(IFoo::class, Foo::class);
            }
        };
        $this->createDispatcher()->dispatch([$binder], $this->container);
        /** @var FactoryContainerBinding $lazyBinding */
        $lazyBinding = $this->container->getBinding(IFoo::class);
        $this->assertInstanceOf(FactoryContainerBinding::class, $lazyBinding);
        $this->assertInstanceOf(Foo::class, ($lazyBinding->factory)());
        /** @var ClassContainerBinding $bindingFromBinder */
        $bindingFromBinder = $this->container->getBinding(IFoo::class);
        $this->assertInstanceOf(ClassContainerBinding::class, $bindingFromBinder);
        $this->assertSame(Foo::class, $bindingFromBinder->concreteClass);
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
