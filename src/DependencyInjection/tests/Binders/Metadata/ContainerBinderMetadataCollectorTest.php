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

use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\Binders\Metadata\ContainerBinderMetadataCollector;
use Aphiria\DependencyInjection\Binders\Metadata\FailedBinderMetadataCollectionException;
use Aphiria\DependencyInjection\Container;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\TargetedContext;
use Aphiria\DependencyInjection\Tests\Binders\Metadata\Mocks\Foo;
use Aphiria\DependencyInjection\Tests\Mocks\Bar;
use Aphiria\DependencyInjection\Tests\Mocks\IFoo;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ContainerBinderMetadataCollectorTest extends TestCase
{
    private IContainer $container;

    protected function setUp(): void
    {
        // Use a real container to simplify testing
        $this->container = new Container();
    }

    public function getBinders(): array
    {
        $binder1 = new class () extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindFactory(IFoo::class, fn () => new Foo());
            }
        };
        $binder2 = new class () extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindInstance(IFoo::class, new Foo());
            }
        };
        $binder3 = new class () extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindClass(IFoo::class, Foo::class);
            }
        };
        $target = new class () {
        };
        $binder4 = new class () extends Binder {
            public object $target;

            public function bind(IContainer $container): void
            {
                $container->for($this->target::class, function (IContainer $container) {
                    $container->bindFactory(IFoo::class, fn () => new Foo());
                });
            }
        };
        $binder4->target = $target;
        $binder5 = new class () extends Binder {
            public object $target;

            public function bind(IContainer $container): void
            {
                $container->for($this->target::class, function (IContainer $container) {
                    $container->bindInstance(IFoo::class, new Foo());
                });
            }
        };
        $binder5->target = $target;
        $binder6 = new class () extends Binder {
            public object $target;

            public function bind(IContainer $container): void
            {
                $container->for($this->target::class, function (IContainer $container) {
                    $container->bindClass(IFoo::class, Foo::class);
                });
            }
        };
        $binder6->target = $target;

        return [
            [$binder1, IFoo::class, false, null],
            [$binder2, IFoo::class, false, null],
            [$binder3, IFoo::class, false, null],
            [$binder4, IFoo::class, true, $target::class],
            [$binder5, IFoo::class, true, $target::class],
            [$binder6, IFoo::class, true, $target::class]
        ];
    }

    /**
     * @param Binder $binder The binder to test with
     * @param string $expectedInterface The expected interface
     * @param bool $isTargeted Whether or not the binding is targeted
     * @param class-string|null $targetClass The target class if there is one, otherwise null
     */
    #[DataProvider('getBinders')]
    public function testBindingMethodsCreatesUniversalBoundInterfaces(Binder $binder, string $expectedInterface, bool $isTargeted, ?string $targetClass): void
    {
        $collector = new ContainerBinderMetadataCollector($this->container);
        $binderMetadata = $collector->collect($binder);
        $actualBoundInterfaces = $binderMetadata->boundInterfaces;
        $this->assertCount(1, $actualBoundInterfaces);
        $this->assertSame($expectedInterface, $actualBoundInterfaces[0]->interface);
        $this->assertSame($isTargeted, $actualBoundInterfaces[0]->context->isTargeted);
        $this->assertSame($targetClass, $actualBoundInterfaces[0]->context->targetClass);
    }

    public function testBindingSameInterfaceButWithOneTargetedAndOneUniversalBindingReturnsTwoBoundInterfaces(): void
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
                $container->bindInstance(IFoo::class, new Foo());
            }
        };
        $binder->target = $target;
        $collector = new ContainerBinderMetadataCollector($this->container);
        $binderMetadata = $collector->collect($binder);
        $actualBoundInterfaces = $binderMetadata->boundInterfaces;
        $this->assertCount(2, $actualBoundInterfaces);
        $this->assertSame($target::class, $actualBoundInterfaces[0]->context->targetClass);
        $this->assertSame(IFoo::class, $actualBoundInterfaces[0]->interface);
        $this->assertFalse($actualBoundInterfaces[1]->context->isTargeted);
        $this->assertSame(IFoo::class, $actualBoundInterfaces[1]->interface);
    }

    public function testBindingSameTargetedInterfaceTwiceReturnsOneBoundInterface(): void
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
                $container->for(new TargetedContext($this->target::class), function (IContainer $container) {
                    $container->bindInstance(IFoo::class, new Foo());
                });
            }
        };
        $binder->target = $target;
        $collector = new ContainerBinderMetadataCollector($this->container);
        $binderMetadata = $collector->collect($binder);
        $actualBoundInterfaces = $binderMetadata->boundInterfaces;
        $this->assertCount(1, $actualBoundInterfaces);
        $this->assertSame($target::class, $actualBoundInterfaces[0]->context->targetClass);
        $this->assertSame(IFoo::class, $actualBoundInterfaces[0]->interface);
    }

    public function testBindingSameUniversalInterfaceTwiceReturnsOneBoundInterface(): void
    {
        $binder = new class () extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindInstance(IFoo::class, new Foo());
                $container->bindInstance(IFoo::class, new Foo());
            }
        };
        $collector = new ContainerBinderMetadataCollector($this->container);
        $binderMetadata = $collector->collect($binder);
        $actualBoundInterfaces = $binderMetadata->boundInterfaces;
        $this->assertCount(1, $actualBoundInterfaces);
        $this->assertFalse($actualBoundInterfaces[0]->context->isTargeted);
        $this->assertSame(IFoo::class, $actualBoundInterfaces[0]->interface);
    }

    public function testCallClosurePassesThroughToComposedContainer(): void
    {
        $closure = fn (int $foo): mixed => null;
        $primitives = [1];
        $container = $this->createMock(IContainer::class);
        $container->expects($this->once())
            ->method('callClosure')
            ->with($closure, $primitives)
            ->willReturn(true);
        $collector = new ContainerBinderMetadataCollector($container);
        $collector->callClosure($closure, $primitives);
    }

    public function testCallMethodPassesThroughToComposedContainer(): void
    {
        $class = new class () {
            public function foo(): bool
            {
                return true;
            }
        };
        $container = $this->createMock(IContainer::class);
        $container->expects($this->once())
            ->method('callMethod')
            ->with($class, 'foo', [1], false)
            ->willReturn(true);
        $collector = new ContainerBinderMetadataCollector($container);
        $collector->callMethod($class, 'foo', [1]);
    }

    public function testForWithStringContextCreatesTargetedBinding(): void
    {
        $target = new class () {
        };
        $this->container->for($target::class, fn (IContainer $container) => $container->bindInstance(IFoo::class, new Bar()));
        $collector = new ContainerBinderMetadataCollector($this->container);
        $collector->for($target::class, function (IContainer $container) {
            $this->assertInstanceOf(Bar::class, $container->resolve(IFoo::class));
        });
    }

    public function testHasBindingPassesThroughToComposedContainerWithCurrentContext(): void
    {
        $collector = new ContainerBinderMetadataCollector($this->container);
        $this->assertFalse($collector->hasBinding(IFoo::class));
        $target = new class () {
        };
        $this->container->for($target::class, fn (IContainer $container) => $container->bindInstance(IFoo::class, new Foo()));
        $this->assertFalse($collector->hasBinding(IFoo::class));
        $this->assertTrue($collector->for($target::class, fn (IContainer $container) => $container->hasBinding(IFoo::class)));
    }

    public function testResolveAddsResolvedBindingEvenIfResolutionFailed(): void
    {
        $binder = new class () extends Binder {
            public function bind(IContainer $container): void
            {
                $container->resolve(IFoo::class);
            }
        };
        $collector = new ContainerBinderMetadataCollector($this->container);

        try {
            $collector->collect($binder);
            $this->fail('Expected to throw exception');
        } catch (FailedBinderMetadataCollectionException $ex) {
            $this->assertCount(1, $ex->incompleteBinderMetadata->resolvedInterfaces);
            $this->assertSame(IFoo::class, $ex->incompleteBinderMetadata->resolvedInterfaces[0]->interface);
        } catch (Exception $ex) {
            $this->fail('Expected ' . FailedBinderMetadataCollectionException::class . ' to be thrown');
        }
    }

    public function testResolvingMethodsCreatesTargetedResolvedInterfaces(): void
    {
        $target = new class () {
        };
        $this->container->for(new TargetedContext($target::class), function (IContainer $container) {
            $container->bindInstance(IFoo::class, new Foo());
            $container->bindInstance(Foo::class, new Foo());
        });
        $binder = new class () extends Binder {
            public object $target;

            public function bind(IContainer $container): void
            {
                $container->for(new TargetedContext($this->target::class), function (IContainer $container) {
                    $container->resolve(IFoo::class);
                    $foo = null;
                    $container->tryResolve(Foo::class, $foo);
                });
            }
        };
        $binder->target = $target;
        $collector = new ContainerBinderMetadataCollector($this->container);
        $binderMetadata = $collector->collect($binder);
        $actualResolvedInterfaces = $binderMetadata->resolvedInterfaces;
        $this->assertCount(2, $actualResolvedInterfaces);
        $this->assertSame($target::class, $actualResolvedInterfaces[0]->context->targetClass);
        $this->assertSame(IFoo::class, $actualResolvedInterfaces[0]->interface);
        $this->assertSame($target::class, $actualResolvedInterfaces[1]->context->targetClass);
        $this->assertSame(Foo::class, $actualResolvedInterfaces[1]->interface);
    }

    public function testResolvingMethodsCreatesUniversalResolvedInterfaces(): void
    {
        $this->container->bindInstance(IFoo::class, new Foo());
        $this->container->bindInstance(Foo::class, new Foo());
        $binder = new class () extends Binder {
            public function bind(IContainer $container): void
            {
                $container->resolve(IFoo::class);
                $foo = null;
                $container->tryResolve(Foo::class, $foo);
            }
        };
        $collector = new ContainerBinderMetadataCollector($this->container);
        $binderMetadata = $collector->collect($binder);
        $actualResolvedInterfaces = $binderMetadata->resolvedInterfaces;
        $this->assertCount(2, $actualResolvedInterfaces);
        $this->assertSame(IFoo::class, $actualResolvedInterfaces[0]->interface);
        $this->assertFalse($actualResolvedInterfaces[0]->context->isTargeted);
        $this->assertSame(Foo::class, $actualResolvedInterfaces[1]->interface);
        $this->assertFalse($actualResolvedInterfaces[1]->context->isTargeted);
    }

    public function testResolvingSameInterfaceButWithOneTargetedAndOneUniversalResolutionReturnsTwoResolvedInterfaces(): void
    {
        $target = new class () {
        };
        $this->container->for(new TargetedContext($target::class), fn (IContainer $container) => $container->bindInstance(IFoo::class, new Foo()));
        $this->container->bindInstance(IFoo::class, new Foo());
        $binder = new class () extends Binder {
            public object $target;

            public function bind(IContainer $container): void
            {
                $container->for(new TargetedContext($this->target::class), function (IContainer $container) {
                    $container->resolve(IFoo::class);
                });
                $container->resolve(IFoo::class);
            }
        };
        $binder->target = $target;
        $collector = new ContainerBinderMetadataCollector($this->container);
        $binderMetadata = $collector->collect($binder);
        $actualResolvedInterfaces = $binderMetadata->resolvedInterfaces;
        $this->assertCount(2, $actualResolvedInterfaces);
        $this->assertSame($target::class, $actualResolvedInterfaces[0]->context->targetClass);
        $this->assertSame(IFoo::class, $actualResolvedInterfaces[0]->interface);
        $this->assertFalse($actualResolvedInterfaces[1]->context->isTargeted);
        $this->assertSame(IFoo::class, $actualResolvedInterfaces[1]->interface);
    }

    public function testResolvingSameTargetedInterfaceTwiceReturnsOneResolvedInterface(): void
    {
        $target = new class () {
        };
        $this->container->for(new TargetedContext($target::class), fn (IContainer $container) => $container->bindInstance(IFoo::class, new Foo()));
        $binder = new class () extends Binder {
            public object $target;
            public function bind(IContainer $container): void
            {
                $container->for(new TargetedContext($this->target::class), function (IContainer $container) {
                    $container->resolve(IFoo::class);
                    $container->resolve(IFoo::class);
                });
            }
        };
        $binder->target = $target;
        $collector = new ContainerBinderMetadataCollector($this->container);
        $binderMetadata = $collector->collect($binder);
        $actualResolvedInterfaces = $binderMetadata->resolvedInterfaces;
        $this->assertCount(1, $actualResolvedInterfaces);
        $this->assertSame($target::class, $actualResolvedInterfaces[0]->context->targetClass);
        $this->assertSame(IFoo::class, $actualResolvedInterfaces[0]->interface);
    }

    public function testResolvingSameUniversalInterfaceTwiceReturnsOneResolvedInterface(): void
    {
        $binder = new class () extends Binder {
            public function bind(IContainer $container): void
            {
                $container->resolve(self::class);
                $container->resolve(self::class);
            }
        };
        $collector = new ContainerBinderMetadataCollector($this->container);
        $binderMetadata = $collector->collect($binder);
        $actualResolvedInterfaces = $binderMetadata->resolvedInterfaces;
        $this->assertCount(1, $actualResolvedInterfaces);
        $this->assertFalse($actualResolvedInterfaces[0]->context->isTargeted);
        $this->assertSame($binder::class, $actualResolvedInterfaces[0]->interface);
    }

    public function testTryResolveAddsResolvedBindingEventIfResolutionFailed(): void
    {
        $binder = new class () extends Binder {
            public function bind(IContainer $container): void
            {
                $foo = null;
                $container->tryResolve(self::class, $foo);
            }
        };
        $collector = new ContainerBinderMetadataCollector($this->container);
        $this->assertCount(1, $collector->collect($binder)->resolvedInterfaces);
        $this->assertSame($binder::class, $collector->collect($binder)->resolvedInterfaces[0]->interface);
    }

    public function testTryResolveResolvesInterfaceUsingComposedContainer(): void
    {
        $this->container->bindInstance(self::class, $this);
        $collector = new ContainerBinderMetadataCollector($this->container);
        $instance = null;
        $this->assertTrue($collector->tryResolve(self::class, $instance));
        $this->assertSame($this, $instance);
    }

    public function testUnbindPassesThroughToComposedContainerWithCurrentContext(): void
    {
        $target = new class () {
        };
        $collector = new ContainerBinderMetadataCollector($this->container);
        $collector->for($target::class, fn (IContainer $container) => $container->bindInstance(IFoo::class, new Foo()));
        $this->assertTrue($collector->for($target::class, fn (IContainer $container) => $container->hasBinding(IFoo::class)));
        $collector->for($target::class, fn (IContainer $container) => $container->unbind(IFoo::class));
        $this->assertFalse($collector->for($target::class, fn (IContainer $container) => $container->hasBinding(IFoo::class)));
    }
}
