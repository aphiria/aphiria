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
use Aphiria\DependencyInjection\Binders\Metadata\BinderMetadata;
use Aphiria\DependencyInjection\Binders\Metadata\BinderMetadataCollection;
use Aphiria\DependencyInjection\Binders\Metadata\BinderMetadataCollectionFactory;
use Aphiria\DependencyInjection\Binders\Metadata\BoundInterface;
use Aphiria\DependencyInjection\Binders\Metadata\ImpossibleBindingException;
use Aphiria\DependencyInjection\Binders\Metadata\ResolvedInterface;
use Aphiria\DependencyInjection\Container;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\Tests\Binders\Metadata\Mocks\Bar;
use Aphiria\DependencyInjection\Tests\Binders\Metadata\Mocks\Foo;
use Aphiria\DependencyInjection\Tests\Binders\Metadata\Mocks\IBar;
use Aphiria\DependencyInjection\Tests\Binders\Metadata\Mocks\IFoo;
use PHPUnit\Framework\TestCase;

/**
 * Tests the binder metadata collection factory
 */
class BinderMetadataCollectionFactoryTest extends TestCase
{
    private BinderMetadataCollectionFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new BinderMetadataCollectionFactory(new Container());
    }

    public function testCreatingCollectionCreatesBindingsFromWhatIsBoundInBinder(): void
    {
        $binder = new class extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindInstance(IFoo::class, new Foo());
            }
        };
        $expectedCollection = new BinderMetadataCollection([
            new BinderMetadata($binder, [new BoundInterface(IFoo::class)], [])
        ]);
        $actualCollection = $this->factory->createBinderMetadataCollection([$binder]);
        $this->assertEquals($expectedCollection, $actualCollection);
    }

    public function testCreatingCollectionThatNeedsTargetedBindingWorksWhenOneHasUniversalBinding(): void
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
        // Binder B will be before binder A because it isn't dependent on another binder's bindings
        $expectedCollection = new BinderMetadataCollection([
            new BinderMetadata($binderB, [new BoundInterface(IFoo::class)], []),
            new BinderMetadata($binderA, [], [new ResolvedInterface(IFoo::class, 'SomeClass')])
        ]);
        $actualCollection = $this->factory->createBinderMetadataCollection([$binderA, $binderB]);
        $this->assertEquals($expectedCollection, $actualCollection);
    }

    public function testCreatingCollectionThatNeedsUniversalBindingThrowsExceptionWhenAnotherOneHasTargetedBinding(): void
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
        $this->factory->createBinderMetadataCollection([$binderA, $binderB]);
    }

    public function testCreatingCollectionThatReliesOnBindingSetInAnotherStillWorks(): void
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
        // Binder B will be before binder A because it isn't dependent on another binder's bindings
        $expectedCollection = new BinderMetadataCollection([
            new BinderMetadata($binderB, [new BoundInterface(IFoo::class)], []),
            new BinderMetadata($binderA, [new BoundInterface('foo')], [new ResolvedInterface(IFoo::class)])
        ]);
        $actualCollection = $this->factory->createBinderMetadataCollection([$binderA, $binderB]);
        $this->assertEquals($expectedCollection, $actualCollection);
    }

    public function testCreatingCollectionThatReliesOnTargetedBindingSetInAnotherStillWorks(): void
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
        // Binder B will be before binder A because it isn't dependent on another binder's bindings
        $expectedCollection = new BinderMetadataCollection([
            new BinderMetadata($binderB, [new BoundInterface(IFoo::class, 'SomeClass')], []),
            new BinderMetadata($binderA, [new BoundInterface(IBar::class, 'SomeClass')], [new ResolvedInterface(IFoo::class, 'SomeClass')])
        ]);
        $actualCollection = $this->factory->createBinderMetadataCollection([$binderA, $binderB]);
        $this->assertEquals($expectedCollection, $actualCollection);
    }

    public function testCreatingCollectionThatMustRetryBinderKeepsTrackOfResolutionsThatWorkedTheSecondTime(): void
    {
        $this->expectException(ImpossibleBindingException::class);
        $binderA = new class extends Binder {
            public function bind(IContainer $container): void
            {
                // This will fail the first time, but should pass the second time
                $container->resolve(IFoo::class);
                $container->bindPrototype('foo', 'bar');
                // This will continue to not be able to be resolved
                $container->resolve(IBar::class);
            }
        };
        $binderB = new class extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindInstance(IFoo::class, new Foo());
            }
        };
        $this->expectExceptionMessage(
            (new ImpossibleBindingException([IBar::class => [$binderA]]))->getMessage(),
            );
        $this->factory->createBinderMetadataCollection([$binderA, $binderB]);
    }

    public function testCreatingCollectionThatReliesOnMultipleOtherBindersBindingStillWorks(): void
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
        // Binder B will be before binder A because it isn't dependent on another binder's bindings
        $expectedCollection = new BinderMetadataCollection([
            new BinderMetadata($binderB, [new BoundInterface(IFoo::class)], []),
            new BinderMetadata($binderC, [new BoundInterface(IBar::class)], []),
            new BinderMetadata($binderA, [new BoundInterface('foo')], [new ResolvedInterface(IFoo::class), new ResolvedInterface(IBar::class)])
        ]);
        $actualCollection = $this->factory->createBinderMetadataCollection([$binderA, $binderB, $binderC]);
        $this->assertEquals($expectedCollection, $actualCollection);
    }

    public function testCreatingCollectionWithBinderThatCannotResolveSomethingThrowsException(): void
    {
        $this->expectException(ImpossibleBindingException::class);
        $binder = new class extends Binder {
            public function bind(IContainer $container): void
            {
                $container->resolve(IFoo::class);
            }
        };
        $this->factory->createBinderMetadataCollection([$binder]);
    }

    public function testCreatingCollectionWithCyclicalDependenciesThrowsException(): void
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
        $this->factory->createBinderMetadataCollection([$binderA, $binderB]);
    }
}
