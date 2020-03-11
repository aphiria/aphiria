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

use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\Binders\Inspection\BindingInspectionContainer;
use Aphiria\DependencyInjection\Binders\Inspection\BindingInspector;
use Aphiria\DependencyInjection\Binders\Inspection\ImpossibleBindingException;
use Aphiria\DependencyInjection\Binders\Inspection\TargetedBinderBinding;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\Tests\Binders\Inspection\Mocks\Bar;
use Aphiria\DependencyInjection\Tests\Binders\Inspection\Mocks\Foo;
use Aphiria\DependencyInjection\Tests\Binders\Inspection\Mocks\IBar;
use Aphiria\DependencyInjection\Tests\Binders\Inspection\Mocks\IFoo;
use PHPUnit\Framework\TestCase;

/**
 * Tests the binding inspector
 */
class BindingInspectorTest extends TestCase
{
    private BindingInspector $inspector;
    private BindingInspectionContainer $container;

    protected function setUp(): void
    {
        $this->container = new BindingInspectionContainer();
        $this->inspector = new BindingInspector($this->container);
    }

    public function testInspectingBindingForBinderThatCannotResolveSomethingThrowsException(): void
    {
        $this->expectException(ImpossibleBindingException::class);
        $binder = new class extends Binder {
            public function bind(IContainer $container): void
            {
                $container->resolve(IFoo::class);
            }
        };
        $this->inspector->getBindings([$binder]);
    }

    public function testInspectingBindersWithCyclicalDependenciesThrowsException(): void
    {
        $this->expectException(ImpossibleBindingException::class);
        $binderA = new class extends Binder {
            public function bind(IContainer $container): void
            {
                /*
                 * Order here is important - a truly cyclical dependency means those dependencies are resolved prior
                 * to them being bound
                 */
                $container->resolve(IFoo::class);
                $container->bindInstance(IBar::class, new Bar());
            }
        };
        $binderB = new class extends Binder {
            public function bind(IContainer $container): void
            {
                // Ditto about order being important
                $container->resolve(IBar::class);
                $container->bindInstance(IFoo::class, new Foo());
            }
        };
        $this->inspector->getBindings([$binderA, $binderB]);
    }

    public function testInspectingBinderThatNeedsTargetedBindingWorksWhenOneHasUniversalBinding(): void
    {
        $binderA = new class extends Binder {
            public function bind(IContainer $container): void
            {
                $container->for('SomeClass', function (IContainer $container) {
                    $container->resolve(IFoo::class);
                });
            }
        };
        $binderB = new class extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindInstance(IFoo::class, new Foo());
            }
        };
        $actualBindings = $this->inspector->getBindings([$binderA, $binderB]);
        $this->assertCount(1, $actualBindings);
        $this->assertEquals(IFoo::class, $actualBindings[0]->getInterface());
        $this->assertSame($binderB, $actualBindings[0]->getBinder());
    }

    public function testInspectingBinderThatNeedsUniversalBindingThrowsExceptionWhenAnotherOneHasTargetedBinding(): void
    {
        $this->expectException(ImpossibleBindingException::class);
        $binderA = new class extends Binder {
            public function bind(IContainer $container): void
            {
                $container->resolve(IFoo::class);
            }
        };
        $binderB = new class extends Binder {
            public function bind(IContainer $container): void
            {
                $container->for('SomeClass', function (IContainer $container) {
                    $container->bindInstance(IFoo::class, new Foo());
                });
            }
        };
        $this->inspector->getBindings([$binderA, $binderB]);
    }

    public function testInspectingBinderThatReliesOnBindingSetInAnotherStillWorks(): void
    {
        $binderA = new class extends Binder {
            public function bind(IContainer $container): void
            {
                $container->resolve(IFoo::class);
                $container->bindPrototype('foo', 'bar');
            }
        };
        $binderB = new class extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindInstance(IFoo::class, new Foo());
            }
        };
        $actualBindings = $this->inspector->getBindings([$binderA, $binderB]);
        $this->assertCount(2, $actualBindings);
        $this->assertEquals(IFoo::class, $actualBindings[0]->getInterface());
        $this->assertSame($binderB, $actualBindings[0]->getBinder());
        $this->assertEquals('foo', $actualBindings[1]->getInterface());
        $this->assertSame($binderA, $actualBindings[1]->getBinder());
    }

    public function testInspectingBinderThatReliesOnTargetedBindingSetInAnotherStillWorks(): void
    {
        $binderA = new class extends Binder {
            public function bind(IContainer $container): void
            {
                $container->for('SomeClass', function (IContainer $container) {
                    $container->resolve(IFoo::class);
                    $container->bindPrototype(IBar::class, Bar::class);
                });
            }
        };
        $binderB = new class extends Binder {
            public function bind(IContainer $container): void
            {
                $container->for('SomeClass', function (IContainer $container) {
                    $container->bindInstance(IFoo::class, new Foo());
                });
            }
        };
        /** @var TargetedBinderBinding[] $actualBindings */
        $actualBindings = $this->inspector->getBindings([$binderA, $binderB]);
        $this->assertCount(2, $actualBindings);
        $this->assertEquals('SomeClass', $actualBindings[0]->getTargetClass());
        $this->assertEquals(IFoo::class, $actualBindings[0]->getInterface());
        $this->assertSame($binderB, $actualBindings[0]->getBinder());
        $this->assertEquals('SomeClass', $actualBindings[1]->getTargetClass());
        $this->assertEquals(IBar::class, $actualBindings[1]->getInterface());
        $this->assertSame($binderA, $actualBindings[1]->getBinder());
    }

    public function testInspectingBinderThatReliesOnMultipleOtherBindersBindingStillWorks(): void
    {
        $binderA = new class extends Binder {
            public function bind(IContainer $container): void
            {
                $container->resolve(IFoo::class);
                $container->bindPrototype('foo', 'bar');
                $container->resolve(IBar::class);
            }
        };
        $binderB = new class extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindInstance(IFoo::class, new Foo());
            }
        };
        $binderC = new class extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindInstance(IBar::class, new Bar());
            }
        };
        $actualBindings = $this->inspector->getBindings([$binderA, $binderB, $binderC]);
        $this->assertCount(3, $actualBindings);
        $this->assertEquals(IFoo::class, $actualBindings[0]->getInterface());
        $this->assertSame($binderB, $actualBindings[0]->getBinder());
        $this->assertEquals('foo', $actualBindings[1]->getInterface());
        $this->assertSame($binderA, $actualBindings[1]->getBinder());
        $this->assertEquals(IBar::class, $actualBindings[2]->getInterface());
        $this->assertSame($binderC, $actualBindings[2]->getBinder());
    }

    public function testInspectingBindingsCreatesBindingsFromWhatIsBoundInBinder(): void
    {
        $expectedBinder = new class extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindInstance(IFoo::class, new Foo());
            }
        };
        $actualBindings = $this->inspector->getBindings([$expectedBinder]);
        $this->assertCount(1, $actualBindings);
        $this->assertEquals(IFoo::class, $actualBindings[0]->getInterface());
        $this->assertSame($expectedBinder, $actualBindings[0]->getBinder());
    }
}
