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
use Aphiria\DependencyInjection\Binders\Inspection\LazyBindingRegistrant;
use Aphiria\DependencyInjection\Binders\Inspection\TargetedBinderBinding;
use Aphiria\DependencyInjection\Binders\Inspection\UniversalBinderBinding;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\Tests\Binders\Inspection\Mocks\Foo;
use Aphiria\DependencyInjection\Tests\Binders\Inspection\Mocks\IFoo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the lazy binding registrant
 */
class LazyBindingRegistrantTest extends TestCase
{
    private LazyBindingRegistrant $registrant;
    /** @var IContainer|MockObject */
    private IContainer $container;

    protected function setUp(): void
    {
        $this->container = $this->createMock(IContainer::class);
        $this->registrant = new LazyBindingRegistrant();
    }

    public function testRegisteringTargetedBindingAndResolvingItWillAddBindingSetInConstructor(): void
    {
        $binder = new class extends Binder {
            /** @var IFoo Used so we can verify what gets bound */
            public $foo;

            public function bind(IContainer $container): void
            {
                $this->foo = new Foo();
                $container->for('bar', function (IContainer $container) {
                    $container->bindInstance(IFoo::class, $this->foo);
                });
            }
        };
        $bindings = [new TargetedBinderBinding('bar', IFoo::class, $binder)];
        $initialCallback = function (IContainer $container) {
            // Don't do anything
        };
        /**
         * NOTE: We won't actually call the for() callbacks until the very end of this test.
         * So, any calls to the container within those callbacks will be executed last, hence the order of expectations.
         */
        // For binding the initial factory
        $this->container->expects($this->at(0))
            ->method('for')
            ->with('bar', $this->callback(function (Closure $callback) use (&$initialCallback) {
                $initialCallback = $callback;

                return true;
            }));
        $initialFactory = function () {
            // Don't do anything
        };
        // Binding the actual factory
        $this->container->expects($this->at(1))
            ->method('bindFactory')
            ->with(IFoo::class, $this->callback(function (Closure $factory) use (&$initialFactory) {
                $initialFactory = $factory;

                return true;
            }));
        $unbindCallback = function (IContainer $container) {
            // Don't do anything
        };
        // For unbinding the initial factory
        $this->container->expects($this->at(2))
            ->method('for')
            ->with('bar', $this->callback(function (Closure $callback) use (&$unbindCallback) {
                $unbindCallback = $callback;

                return true;
            }));
        $binderCallback = function (IContainer $container) {
            // Don't do anything
        };
        // For binding the instance from within the binder
        $this->container->expects($this->at(3))
            ->method('for')
            ->with('bar', $this->callback(function (Closure $callback) use (&$binderCallback) {
                $binderCallback = $callback;

                return true;
            }));
        $resolutionCallback = function (IContainer $container) {
            // Don't do anything
        };
        // For resolving the interface
        $this->container->expects($this->at(4))
            ->method('for')
            ->with('bar', $this->callback(function (Closure $callback) use (&$resolutionCallback) {
                $resolutionCallback = $callback;

                return true;
            }));
        // Unbind the initial factory
        $this->container->expects($this->at(5))
            ->method('unbind')
            ->with(IFoo::class);
        // Bind the instance from within the binder
        $this->container->expects($this->at(6))
            ->method('bindInstance')
            ->with(IFoo::class, $this->callback(fn (Foo $foo) => true));
        $this->registrant->registerBindings($bindings, $this->container);
        $initialCallback($this->container);
        $initialFactory();
        $unbindCallback($this->container);
        $binderCallback($this->container);
        $resolutionCallback($this->container);
    }

    public function testRegisteringTargetedBindingRegistersBindingsFromBinder(): void
    {
        $binder = new class extends Binder {
            public function bind(IContainer $container): void
            {
                $container->for('bar', function (IContainer $container) {
                    $container->bindInstance(IFoo::class, new Foo());
                });
            }
        };
        $bindings = [new TargetedBinderBinding('bar', IFoo::class, $binder)];
        $this->container->expects($this->once())
            ->method('for')
            ->with('bar', $this->callback(function (Closure $factory) {
                return $factory instanceof Closure;
            }));
        $this->registrant->registerBindings($bindings, $this->container);
    }

    public function testRegisteringUniversalBindingAndResolvingItWillAddBindingSetInConstructor(): void
    {
        $binder = new class extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindInstance(IFoo::class, new Foo());
            }
        };
        $bindings = [new UniversalBinderBinding(IFoo::class, $binder)];
        $actualFactory = function () {
            // Don't do anything
        };
        $this->container->expects($this->at(0))
            ->method('bindFactory')
            ->with(IFoo::class, $this->callback(function (Closure $factory) use (&$actualFactory) {
                $actualFactory = $factory;

                return $factory instanceof Closure;
            }));
        $this->container->expects($this->at(1))
            ->method('unbind')
            ->with(IFoo::class);
        $this->container->expects($this->at(2))
            ->method('bindInstance')
            ->with(IFoo::class, $this->callback(function (Foo $foo) {
                return $foo instanceof Foo;
            }));
        $this->registrant->registerBindings($bindings, $this->container);
        $actualFactory();
    }

    public function testRegisteringUniversalBindingRegistersBindingsFromBinder(): void
    {
        $binder = new class extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindInstance(IFoo::class, new Foo());
            }
        };
        $bindings = [new UniversalBinderBinding(IFoo::class, $binder)];
        $this->container->expects($this->once())
            ->method('bindFactory')
            ->with(IFoo::class, $this->callback(function (Closure $factory) {
                return $factory instanceof Closure;
            }));
        $this->registrant->registerBindings($bindings, $this->container);
    }
}
