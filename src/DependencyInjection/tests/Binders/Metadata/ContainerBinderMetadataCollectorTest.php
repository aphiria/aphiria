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

use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\Binders\Metadata\ContainerBinderMetadataCollector;
use Aphiria\DependencyInjection\Binders\Metadata\FailedBinderMetadataCollectionException;
use Aphiria\DependencyInjection\Container;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\Tests\Binders\Metadata\Mocks\Foo;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * Tests the container binder metadata collector
 */
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
                $container->for('bar', function (IContainer $container) {
                    $container->bindFactory('foo0', function () {
                        return new Foo();
                    });
                    $container->bindInstance('foo1', new Foo());
                    $container->bindPrototype('foo2', Foo::class);
                    $container->bindSingleton('foo3', Foo::class);
                });
            }
        };
        $collector = new ContainerBinderMetadataCollector($this->container);
        $binderMetadata = $collector->collect($binder);
        $actualBoundInterfaces = $binderMetadata->getBoundInterfaces();
        $this->assertCount(4, $actualBoundInterfaces);

        foreach ($actualBoundInterfaces as $i => $actualBoundInterface) {
            $this->assertEquals('bar', $actualBoundInterface->getTargetClass());
            $this->assertEquals("foo$i", $actualBoundInterface->getInterface());
        }
    }

    public function testBindingMethodsCreatesUniversalBoundInterfaces(): void
    {
        $binder = new class extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindFactory('foo0', function () {
                    return new Foo();
                });
                $container->bindInstance('foo1', new Foo());
                $container->bindPrototype('foo2', Foo::class);
                $container->bindSingleton('foo3', Foo::class);
            }
        };
        $collector = new ContainerBinderMetadataCollector($this->container);
        $binderMetadata = $collector->collect($binder);
        $actualBoundInterfaces = $binderMetadata->getBoundInterfaces();
        $this->assertCount(4, $actualBoundInterfaces);

        foreach ($actualBoundInterfaces as $i => $actualBoundInterface) {
            $this->assertEquals("foo$i", $actualBoundInterface->getInterface());
            $this->assertFalse($actualBoundInterface->isTargeted());
        }
    }

    public function testBindingSameInterfaceButWithOneTargetedAndOneUniversalBindingReturnsTwoBoundInterfaces(): void
    {
        $binder = new class() extends Binder {
            public function bind(IContainer $container): void
            {
                $container->for('bar', function (IContainer $container) {
                    $container->bindInstance('foo', new Foo());
                });
                $container->bindInstance('foo', new Foo());
            }
        };
        $collector = new ContainerBinderMetadataCollector($this->container);
        $binderMetadata = $collector->collect($binder);
        $actualBoundInterfaces = $binderMetadata->getBoundInterfaces();
        $this->assertCount(2, $actualBoundInterfaces);
        $this->assertEquals('bar', $actualBoundInterfaces[0]->getTargetClass());
        $this->assertEquals('foo', $actualBoundInterfaces[0]->getInterface());
        $this->assertFalse($actualBoundInterfaces[1]->isTargeted());
        $this->assertEquals('foo', $actualBoundInterfaces[1]->getInterface());
    }

    public function testBindingSameTargetedInterfaceTwiceReturnsOneBoundInterface(): void
    {
        $binder = new class() extends Binder {
            public function bind(IContainer $container): void
            {
                $container->for('bar', function (IContainer $container) {
                    $container->bindInstance('foo', new Foo());
                });
                $container->for('bar', function (IContainer $container) {
                    $container->bindInstance('foo', new Foo());
                });
            }
        };
        $collector = new ContainerBinderMetadataCollector($this->container);
        $binderMetadata = $collector->collect($binder);
        $actualBoundInterfaces = $binderMetadata->getBoundInterfaces();
        $this->assertCount(1, $actualBoundInterfaces);
        $this->assertEquals('bar', $actualBoundInterfaces[0]->getTargetClass());
        $this->assertEquals('foo', $actualBoundInterfaces[0]->getInterface());
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
        $this->assertFalse($actualBoundInterfaces[0]->isTargeted());
        $this->assertEquals('foo', $actualBoundInterfaces[0]->getInterface());
    }

    public function testResolveDoesNotAddResolvedBindingIfResolutionFailed(): void
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
            $this->assertEmpty($ex->getIncompleteBinderMetadata()->getResolvedInterfaces());
        } catch (Exception $ex) {
            $this->fail('Expected ' . FailedBinderMetadataCollectionException::class . ' to be thrown');
        }
    }

    public function testResolvingMethodsCreatesTargetedResolvedInterfaces(): void
    {
        $this->container->for('bar', function (IContainer $container) {
            $container->bindInstance('foo0', new Foo());
            $container->bindInstance('foo1', new Foo());
        });
        $binder = new class extends Binder {
            public function bind(IContainer $container): void
            {
                $container->for('bar', function (IContainer $container) {
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
            $this->assertEquals('bar', $actualResolvedInterface->getTargetClass());
            $this->assertEquals("foo$i", $actualResolvedInterface->getInterface());
        }
    }

    public function testResolvingMethodsCreatesUniversalResolvedInterfaces(): void
    {
        $this->container->bindInstance('foo0', new Foo());
        $this->container->bindInstance('foo1', new Foo());
        $binder = new class extends Binder {
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
            $this->assertEquals("foo$i", $actualResolvedInterface->getInterface());
            $this->assertFalse($actualResolvedInterface->isTargeted());
        }
    }

    public function testResolvingSameInterfaceButWithOneTargetedAndOneUniversalResolutionReturnsTwoResolvedInterfaces(): void
    {
        $this->container->for('bar', fn (IContainer $container) => $container->bindInstance('foo', new Foo()));
        $this->container->bindInstance('foo', new Foo());
        $binder = new class() extends Binder {
            public function bind(IContainer $container): void
            {
                $container->for('bar', function (IContainer $container) {
                    $container->resolve('foo');
                });
                $container->resolve('foo');
            }
        };
        $collector = new ContainerBinderMetadataCollector($this->container);
        $binderMetadata = $collector->collect($binder);
        $actualResolvedInterfaces = $binderMetadata->getResolvedInterfaces();
        $this->assertCount(2, $actualResolvedInterfaces);
        $this->assertEquals('bar', $actualResolvedInterfaces[0]->getTargetClass());
        $this->assertEquals('foo', $actualResolvedInterfaces[0]->getInterface());
        $this->assertFalse($actualResolvedInterfaces[1]->isTargeted());
        $this->assertEquals('foo', $actualResolvedInterfaces[1]->getInterface());
    }

    public function testResolvingSameTargetedInterfaceTwiceReturnsOneResolvedInterface(): void
    {
        $this->container->for('bar', fn (IContainer $container) => $container->bindInstance('foo', new Foo()));
        $binder = new class() extends Binder {
            public function bind(IContainer $container): void
            {
                $container->for('bar', function (IContainer $container) {
                    $container->resolve('foo');
                    $container->resolve('foo');
                });
            }
        };
        $collector = new ContainerBinderMetadataCollector($this->container);
        $binderMetadata = $collector->collect($binder);
        $actualResolvedInterfaces = $binderMetadata->getResolvedInterfaces();
        $this->assertCount(1, $actualResolvedInterfaces);
        $this->assertEquals('bar', $actualResolvedInterfaces[0]->getTargetClass());
        $this->assertEquals('foo', $actualResolvedInterfaces[0]->getInterface());
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
        $this->assertFalse($actualResolvedInterfaces[0]->isTargeted());
        $this->assertEquals('foo', $actualResolvedInterfaces[0]->getInterface());
    }

    public function testTryResolveDoesNotAddResolvedBindingIfResolutionFailed(): void
    {
        $binder = new class() extends Binder {
            public function bind(IContainer $container): void
            {
                $foo = null;
                $container->tryResolve('foo', $foo);
            }
        };
        $collector = new ContainerBinderMetadataCollector($this->container);
        $this->assertEmpty($collector->collect($binder)->getResolvedInterfaces());
    }
}
