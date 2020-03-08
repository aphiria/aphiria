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

use Aphiria\DependencyInjection\Binders\Inspection\BindingInspectionContainer;
use Aphiria\DependencyInjection\Binders\Inspection\TargetedBinderBinding;
use Aphiria\DependencyInjection\Binders\Inspection\UniversalBinderBinding;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\Tests\Binders\Inspection\Mocks\Foo;
use Aphiria\DependencyInjection\Tests\Binders\Inspection\Mocks\IFoo;
use Aphiria\DependencyInjection\Tests\Binders\Mocks\Binder;
use PHPUnit\Framework\TestCase;

/**
 * Tests the binding inspection container
 */
class BindingInspectionContainerTest extends TestCase
{
    private BindingInspectionContainer $container;

    protected function setUp(): void
    {
        $this->container = new BindingInspectionContainer();
    }

    public function testBindingMethodsCreatesTargetedBindings(): void
    {
        $expectedBinder = new class extends Binder {
            public function bind(IContainer $container): void
            {
                $container->for('bar', function (IContainer $container) {
                    $container->bindFactory(IFoo::class, function () {
                        return new Foo();
                    });
                    $container->bindInstance(IFoo::class, new Foo());
                    $container->bindPrototype(IFoo::class, Foo::class);
                    $container->bindSingleton(IFoo::class, Foo::class);
                });
            }
        };
        $this->container->setBinder($expectedBinder);
        $expectedBinder->bind($this->container);
        $actualBindings = $this->container->getBindings();

        /** @var TargetedBinderBinding $actualBinding */
        foreach ($actualBindings as $actualBinding) {
            $this->assertInstanceOf(TargetedBinderBinding::class, $actualBinding);
            $this->assertEquals('bar', $actualBinding->getTargetClass());
            $this->assertEquals(IFoo::class, $actualBinding->getInterface());
            $this->assertSame($expectedBinder, $actualBinding->getBinder());
        }
    }

    public function testBindingMethodsCreatesUniversalBindings(): void
    {
        $expectedBinder = new class extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindFactory(IFoo::class, function () {
                    return new Foo();
                });
                $container->bindInstance(IFoo::class, new Foo());
                $container->bindPrototype(IFoo::class, Foo::class);
                $container->bindSingleton(IFoo::class, Foo::class);
            }
        };
        $this->container->setBinder($expectedBinder);
        $expectedBinder->bind($this->container);
        $actualBindings = $this->container->getBindings();

        foreach ($actualBindings as $actualBinding) {
            $this->assertInstanceOf(UniversalBinderBinding::class, $actualBinding);
            $this->assertEquals(IFoo::class, $actualBinding->getInterface());
            $this->assertSame($expectedBinder, $actualBinding->getBinder());
        }
    }

    public function testBindingSameTargetedBindingTwiceOnlyRegistersItOnce(): void
    {
        $binder = new class extends Binder {
            public function bind(IContainer $container): void
            {
                $container->for('foo', function (IContainer $container) {
                    $container->bindSingleton(IFoo::class, Foo::class);
                });
            }
        };
        $this->container->setBinder($binder);
        $binder->bind($this->container);
        // Re-register bindings
        $binder->bind($this->container);
        /** @var TargetedBinderBinding[] $actualBindings */
        $actualBindings = $this->container->getBindings();
        $this->assertCount(1, $actualBindings);
        $this->assertEquals('foo', $actualBindings[0]->getTargetClass());
        $this->assertEquals(IFoo::class, $actualBindings[0]->getInterface());
        $this->assertSame($binder, $actualBindings[0]->getBinder());
    }

    public function testBindingSameUniversalBindingTwiceOnlyRegistersItOnce(): void
    {
        $binder = new class extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindSingleton(IFoo::class, Foo::class);
            }
        };
        $this->container->setBinder($binder);
        $binder->bind($this->container);
        // Re-register bindings
        $binder->bind($this->container);
        $actualBindings = $this->container->getBindings();
        $this->assertCount(1, $actualBindings);
        $this->assertEquals(IFoo::class, $actualBindings[0]->getInterface());
        $this->assertSame($binder, $actualBindings[0]->getBinder());
    }
}
