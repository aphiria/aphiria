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

use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\Binders\Metadata\ContainerBinderMetadataCollector;
use Aphiria\DependencyInjection\Binders\Metadata\FailedBinderMetadataCollectionException;
use Aphiria\DependencyInjection\Container;
use Aphiria\DependencyInjection\Context;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\TargetedContext;
use Aphiria\DependencyInjection\Tests\Binders\Metadata\Mocks\Foo;
use Aphiria\DependencyInjection\Tests\Mocks\Bar;
use Aphiria\DependencyInjection\Tests\Mocks\IFoo;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ContainerBinderMetadataCollectorTest extends TestCase
{
    private IContainer $container;

    protected function setUp(): void
    {
        // Use a real container to simplify testing
        $this->container = new Container();
    }

    public function testBindingMethodsCreatesTargetedBoundInterfaces(): void
    {
        $binder = new class() extends Binder {
            public function bind(IContainer $container): void
            {
                $container->for(new TargetedContext('bar'), function (IContainer $container) {
                    $container->bindFactory('foo0', function () {
                        return new Foo();
                    });
                    $container->bindInstance('foo1', new Foo());
                    $container->bindClass('foo2', Foo::class);
                });
            }
        };
        $collector = new ContainerBinderMetadataCollector($this->container);
        $binderMetadata = $collector->collect($binder);
        $actualBoundInterfaces = $binderMetadata->getBoundInterfaces();
        $this->assertCount(3, $actualBoundInterfaces);

        foreach ($actualBoundInterfaces as $i => $actualBoundInterface) {
            $this->assertSame('bar', $actualBoundInterface->getContext()->getTargetClass());
            $this->assertSame("foo$i", $actualBoundInterface->getInterface());
        }
    }

    public function testBindingMethodsCreatesUniversalBoundInterfaces(): void
    {
        $binder = new class() extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindFactory('foo0', function () {
                    return new Foo();
                });
                $container->bindInstance('foo1', new Foo());
                $container->bindClass('foo2', Foo::class);
            }
        };
        $collector = new ContainerBinderMetadataCollector($this->container);
        $binderMetadata = $collector->collect($binder);
        $actualBoundInterfaces = $binderMetadata->getBoundInterfaces();
        $this->assertCount(3, $actualBoundInterfaces);

        foreach ($actualBoundInterfaces as $i => $actualBoundInterface) {
            $this->assertSame("foo$i", $actualBoundInterface->getInterface());
            $this->assertFalse($actualBoundInterface->getContext()->isTargeted());
        }
    }

    public function testBindingSameInterfaceButWithOneTargetedAndOneUniversalBindingReturnsTwoBoundInterfaces(): void
    {
        $binder = new class() extends Binder {
            public function bind(IContainer $container): void
            {
                $container->for(new TargetedContext('bar'), function (IContainer $container) {
                    $container->bindInstance('foo', new Foo());
                });
                $container->bindInstance('foo', new Foo());
            }
        };
        $collector = new ContainerBinderMetadataCollector($this->container);
        $binderMetadata = $collector->collect($binder);
        $actualBoundInterfaces = $binderMetadata->getBoundInterfaces();
        $this->assertCount(2, $actualBoundInterfaces);
        $this->assertSame('bar', $actualBoundInterfaces[0]->getContext()->getTargetClass());
        $this->assertSame('foo', $actualBoundInterfaces[0]->getInterface());
        $this->assertFalse($actualBoundInterfaces[1]->getContext()->isTargeted());
        $this->assertSame('foo', $actualBoundInterfaces[1]->getInterface());
    }

    public function testBindingSameTargetedInterfaceTwiceReturnsOneBoundInterface(): void
    {
        $binder = new class() extends Binder {
            public function bind(IContainer $container): void
            {
                $container->for(new TargetedContext('bar'), function (IContainer $container) {
                    $container->bindInstance('foo', new Foo());
                });
                $container->for(new TargetedContext('bar'), function (IContainer $container) {
                    $container->bindInstance('foo', new Foo());
                });
            }
        };
        $collector = new ContainerBinderMetadataCollector($this->container);
        $binderMetadata = $collector->collect($binder);
        $actualBoundInterfaces = $binderMetadata->getBoundInterfaces();
        $this->assertCount(1, $actualBoundInterfaces);
        $this->assertSame('bar', $actualBoundInterfaces[0]->getContext()->getTargetClass());
        $this->assertSame('foo', $actualBoundInterfaces[0]->getInterface());
    }

    public function testBindingSameUniversalInterfaceTwiceReturnsOneBoundInterface(): void
    {
        $binder = new class() extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindInstance('foo', new Foo());
                $container->bindInstance('foo', new Foo());
            }
        };
        $collector = new ContainerBinderMetadataCollector($this->container);
        $binderMetadata = $collector->collect($binder);
        $actualBoundInterfaces = $binderMetadata->getBoundInterfaces();
        $this->assertCount(1, $actualBoundInterfaces);
        $this->assertFalse($actualBoundInterfaces[0]->getContext()->isTargeted());
        $this->assertSame('foo', $actualBoundInterfaces[0]->getInterface());
    }

    public function testCallClosurePassesThroughToComposedContainer(): void
    {
        $closure = fn (int $foo) => null;
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
        $class = new class() {
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

    public function testForWithInvalidParameterThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Context must be an instance of ' . Context::class . ' or string');
        $collector = new ContainerBinderMetadataCollector($this->container);
        $collector->for(1, fn (IContainer $container) => null);
    }

    public function testForWithStringContextCreatesTargetedBinding(): void
    {
        $this->container->for('foo', fn (IContainer $container) => $container->bindInstance(IFoo::class, new Bar()));
        $collector = new ContainerBinderMetadataCollector($this->container);
        $collector->for('foo', function (IContainer $container) {
            $this->assertInstanceOf(Bar::class, $container->resolve(IFoo::class));
        });
    }

    public function testHasBindingPassesThroughToComposedContainerWithCurrentContext(): void
    {
        $collector = new ContainerBinderMetadataCollector($this->container);
        $this->assertFalse($collector->hasBinding(IFoo::class));
        $this->container->for('foo', fn (IContainer $container) => $container->bindInstance(IFoo::class, new Foo()));
        $this->assertFalse($collector->hasBinding(IFoo::class));
        $this->assertTrue($collector->for('foo', fn (IContainer $container) => $container->hasBinding(IFoo::class)));
    }

    public function testResolveAddsResolvedBindingEvenIfResolutionFailed(): void
    {
        $binder = new class() extends Binder {
            public function bind(IContainer $container): void
            {
                $container->resolve('foo');
            }
        };
        $collector = new ContainerBinderMetadataCollector($this->container);

        try {
            $collector->collect($binder);
            $this->fail('Expected to throw exception');
        } catch (FailedBinderMetadataCollectionException $ex) {
            $this->assertCount(1, $ex->getIncompleteBinderMetadata()->getResolvedInterfaces());
            $this->assertSame('foo', $ex->getIncompleteBinderMetadata()->getResolvedInterfaces()[0]->getInterface());
        } catch (Exception $ex) {
            $this->fail('Expected ' . FailedBinderMetadataCollectionException::class . ' to be thrown');
        }
    }

    public function testResolvingMethodsCreatesTargetedResolvedInterfaces(): void
    {
        $this->container->for(new TargetedContext('bar'), function (IContainer $container) {
            $container->bindInstance('foo0', new Foo());
            $container->bindInstance('foo1', new Foo());
        });
        $binder = new class() extends Binder {
            public function bind(IContainer $container): void
            {
                $container->for(new TargetedContext('bar'), function (IContainer $container) {
                    $container->resolve('foo0');
                    $foo = null;
                    $container->tryResolve('foo1', $foo);
                });
            }
        };
        $collector = new ContainerBinderMetadataCollector($this->container);
        $binderMetadata = $collector->collect($binder);
        $actualResolvedInterfaces = $binderMetadata->getResolvedInterfaces();
        $this->assertCount(2, $actualResolvedInterfaces);

        foreach ($actualResolvedInterfaces as $i => $actualResolvedInterface) {
            $this->assertSame('bar', $actualResolvedInterface->getContext()->getTargetClass());
            $this->assertSame("foo$i", $actualResolvedInterface->getInterface());
        }
    }

    public function testResolvingMethodsCreatesUniversalResolvedInterfaces(): void
    {
        $this->container->bindInstance('foo0', new Foo());
        $this->container->bindInstance('foo1', new Foo());
        $binder = new class() extends Binder {
            public function bind(IContainer $container): void
            {
                $container->resolve('foo0');
                $foo = null;
                $container->tryResolve('foo1', $foo);
            }
        };
        $collector = new ContainerBinderMetadataCollector($this->container);
        $binderMetadata = $collector->collect($binder);
        $actualResolvedInterfaces = $binderMetadata->getResolvedInterfaces();
        $this->assertCount(2, $actualResolvedInterfaces);

        foreach ($actualResolvedInterfaces as $i => $actualResolvedInterface) {
            $this->assertSame("foo$i", $actualResolvedInterface->getInterface());
            $this->assertFalse($actualResolvedInterface->getContext()->isTargeted());
        }
    }

    public function testResolvingSameInterfaceButWithOneTargetedAndOneUniversalResolutionReturnsTwoResolvedInterfaces(): void
    {
        $this->container->for(new TargetedContext('bar'), fn (IContainer $container) => $container->bindInstance('foo', new Foo()));
        $this->container->bindInstance('foo', new Foo());
        $binder = new class() extends Binder {
            public function bind(IContainer $container): void
            {
                $container->for(new TargetedContext('bar'), function (IContainer $container) {
                    $container->resolve('foo');
                });
                $container->resolve('foo');
            }
        };
        $collector = new ContainerBinderMetadataCollector($this->container);
        $binderMetadata = $collector->collect($binder);
        $actualResolvedInterfaces = $binderMetadata->getResolvedInterfaces();
        $this->assertCount(2, $actualResolvedInterfaces);
        $this->assertSame('bar', $actualResolvedInterfaces[0]->getContext()->getTargetClass());
        $this->assertSame('foo', $actualResolvedInterfaces[0]->getInterface());
        $this->assertFalse($actualResolvedInterfaces[1]->getContext()->isTargeted());
        $this->assertSame('foo', $actualResolvedInterfaces[1]->getInterface());
    }

    public function testResolvingSameTargetedInterfaceTwiceReturnsOneResolvedInterface(): void
    {
        $this->container->for(new TargetedContext('bar'), fn (IContainer $container) => $container->bindInstance('foo', new Foo()));
        $binder = new class() extends Binder {
            public function bind(IContainer $container): void
            {
                $container->for(new TargetedContext('bar'), function (IContainer $container) {
                    $container->resolve('foo');
                    $container->resolve('foo');
                });
            }
        };
        $collector = new ContainerBinderMetadataCollector($this->container);
        $binderMetadata = $collector->collect($binder);
        $actualResolvedInterfaces = $binderMetadata->getResolvedInterfaces();
        $this->assertCount(1, $actualResolvedInterfaces);
        $this->assertSame('bar', $actualResolvedInterfaces[0]->getContext()->getTargetClass());
        $this->assertSame('foo', $actualResolvedInterfaces[0]->getInterface());
    }

    public function testResolvingSameUniversalInterfaceTwiceReturnsOneResolvedInterface(): void
    {
        $this->container->bindInstance('foo', new Foo());
        $binder = new class() extends Binder {
            public function bind(IContainer $container): void
            {
                $container->resolve('foo');
                $container->resolve('foo');
            }
        };
        $collector = new ContainerBinderMetadataCollector($this->container);
        $binderMetadata = $collector->collect($binder);
        $actualResolvedInterfaces = $binderMetadata->getResolvedInterfaces();
        $this->assertCount(1, $actualResolvedInterfaces);
        $this->assertFalse($actualResolvedInterfaces[0]->getContext()->isTargeted());
        $this->assertSame('foo', $actualResolvedInterfaces[0]->getInterface());
    }

    public function testTryResolveAddsResolvedBindingEventIfResolutionFailed(): void
    {
        $binder = new class() extends Binder {
            public function bind(IContainer $container): void
            {
                $foo = null;
                $container->tryResolve('foo', $foo);
            }
        };
        $collector = new ContainerBinderMetadataCollector($this->container);
        $this->assertCount(1, $collector->collect($binder)->getResolvedInterfaces());
        $this->assertSame('foo', $collector->collect($binder)->getResolvedInterfaces()[0]->getInterface());
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
        $collector = new ContainerBinderMetadataCollector($this->container);
        $collector->for('foo', fn (IContainer $container) => $container->bindInstance(IFoo::class, new Foo()));
        $this->assertTrue($collector->for('foo', fn (IContainer $container) => $container->hasBinding(IFoo::class)));
        $collector->for('foo', fn (IContainer $container) => $container->unbind(IFoo::class));
        $this->assertFalse($collector->for('foo', fn (IContainer $container) => $container->hasBinding(IFoo::class)));
    }
}
