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
use Aphiria\DependencyInjection\Binders\Metadata\BinderMetadata;
use Aphiria\DependencyInjection\Binders\Metadata\BinderMetadataCollection;
use Aphiria\DependencyInjection\Binders\Metadata\BinderMetadataCollectionFactory;
use Aphiria\DependencyInjection\Binders\Metadata\BoundInterface;
use Aphiria\DependencyInjection\Binders\Metadata\ImpossibleBindingException;
use Aphiria\DependencyInjection\Binders\Metadata\ResolvedInterface;
use Aphiria\DependencyInjection\Container;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\TargetedContext;
use Aphiria\DependencyInjection\Tests\Binders\Metadata\Mocks\Bar;
use Aphiria\DependencyInjection\Tests\Binders\Metadata\Mocks\Foo;
use Aphiria\DependencyInjection\Tests\Binders\Metadata\Mocks\IBar;
use Aphiria\DependencyInjection\Tests\Binders\Metadata\Mocks\IFoo;
use Aphiria\DependencyInjection\Tests\Mocks\Dave;
use Aphiria\DependencyInjection\Tests\Mocks\IPerson;
use Aphiria\DependencyInjection\UniversalContext;
use PHPUnit\Framework\TestCase;

class BinderMetadataCollectionFactoryTest extends TestCase
{
    private BinderMetadataCollectionFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new BinderMetadataCollectionFactory(new Container());
    }

    public function testCreatingCollectionCreatesBindingsFromWhatIsBoundInBinder(): void
    {
        $binder = new class () extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindInstance(IFoo::class, new Foo());
            }
        };
        $expectedCollection = new BinderMetadataCollection([
            new BinderMetadata($binder, [new BoundInterface(IFoo::class, new UniversalContext())], [])
        ]);
        $actualCollection = $this->factory->createBinderMetadataCollection([$binder]);
        $this->assertEquals($expectedCollection, $actualCollection);
    }

    public function testCreatingCollectionThatNeedsTargetedBindingWorksWhenOneHasUniversalBinding(): void
    {
        $target = new class () {
        };
        $binderA = new class () extends Binder {
            public object $target;

            public function bind(IContainer $container): void
            {
                $container->for(new TargetedContext($this->target::class), function (IContainer $container) {
                    $container->resolve(IFoo::class);
                });
            }
        };
        $binderA->target = $target;
        $binderB = new class () extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindInstance(IFoo::class, new Foo());
            }
        };
        // Binder B will be before binder A because it isn't dependent on another binder's bindings
        $expectedCollection = new BinderMetadataCollection([
            new BinderMetadata($binderB, [new BoundInterface(IFoo::class, new UniversalContext())], []),
            new BinderMetadata($binderA, [], [new ResolvedInterface(IFoo::class, new TargetedContext($target::class))])
        ]);
        $actualCollection = $this->factory->createBinderMetadataCollection([$binderA, $binderB]);
        $this->assertEquals($expectedCollection, $actualCollection);
    }

    public function testCreatingCollectionThatNeedsUniversalBindingThrowsExceptionWhenAnotherOneHasTargetedBinding(): void
    {
        $this->expectException(ImpossibleBindingException::class);
        $target = new class () {
        };
        $binderA = new class () extends Binder {
            public function bind(IContainer $container): void
            {
                $container->resolve(IFoo::class);
            }
        };
        $binderB = new class () extends Binder {
            public object $target;

            public function bind(IContainer $container): void
            {
                $container->for(new TargetedContext($this->target::class), function (IContainer $container) {
                    $container->bindInstance(IFoo::class, new Foo());
                });
            }
        };
        $binderB->target = $target;
        $this->factory->createBinderMetadataCollection([$binderA, $binderB]);
    }

    public function testCreatingCollectionThatReliesOnBindingSetInAnotherStillWorks(): void
    {
        $binderA = new class () extends Binder {
            public function bind(IContainer $container): void
            {
                $container->resolve(IFoo::class);
                $container->bindClass(IPerson::class, Dave::class);
            }
        };
        $binderB = new class () extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindInstance(IFoo::class, new Foo());
            }
        };
        // Binder B will be before binder A because it isn't dependent on another binder's bindings
        $expectedCollection = new BinderMetadataCollection([
            new BinderMetadata($binderB, [new BoundInterface(IFoo::class, new UniversalContext())], []),
            new BinderMetadata($binderA, [new BoundInterface(IPerson::class, new UniversalContext())], [new ResolvedInterface(IFoo::class, new UniversalContext())])
        ]);
        $actualCollection = $this->factory->createBinderMetadataCollection([$binderA, $binderB]);
        $this->assertEquals($expectedCollection, $actualCollection);
    }

    public function testCreatingCollectionThatReliesOnTargetedBindingSetInAnotherStillWorks(): void
    {
        $target = new class () {
        };
        $binderA = new class () extends Binder {
            public object $target;

            public function bind(IContainer $container): void
            {
                $container->for(new TargetedContext($this->target::class), function (IContainer $container) {
                    $container->resolve(IFoo::class);
                    $container->bindClass(IBar::class, Bar::class);
                });
            }
        };
        $binderA->target = $target;
        $binderB = new class () extends Binder {
            public object $target;

            public function bind(IContainer $container): void
            {
                $container->for(new TargetedContext($this->target::class), function (IContainer $container) {
                    $container->bindInstance(IFoo::class, new Foo());
                });
            }
        };
        $binderB->target = $target;
        // Binder B will be before binder A because it isn't dependent on another binder's bindings
        $expectedCollection = new BinderMetadataCollection([
            new BinderMetadata($binderB, [new BoundInterface(IFoo::class, new TargetedContext($target::class))], []),
            new BinderMetadata($binderA, [new BoundInterface(IBar::class, new TargetedContext($target::class))], [new ResolvedInterface(IFoo::class, new TargetedContext($target::class))])
        ]);
        $actualCollection = $this->factory->createBinderMetadataCollection([$binderA, $binderB]);
        $this->assertEquals($expectedCollection, $actualCollection);
    }

    public function testCreatingCollectionThatMustRetryBinderKeepsTrackOfResolutionsThatWorkedTheSecondTime(): void
    {
        $this->expectException(ImpossibleBindingException::class);
        $binderA = new class () extends Binder {
            public function bind(IContainer $container): void
            {
                // This will fail the first time, but should pass the second time
                $container->resolve(IFoo::class);
                $container->bindClass(IPerson::class, Dave::class);
                // This will continue to not be able to be resolved
                $container->resolve(IBar::class);
            }
        };
        $binderB = new class () extends Binder {
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
        $binderA = new class () extends Binder {
            public function bind(IContainer $container): void
            {
                $container->resolve(IFoo::class);
                $container->bindClass(IPerson::class, Dave::class);
                $container->resolve(IBar::class);
            }
        };
        $binderB = new class () extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindInstance(IFoo::class, new Foo());
            }
        };
        $binderC = new class () extends Binder {
            public function bind(IContainer $container): void
            {
                $container->bindInstance(IBar::class, new Bar());
            }
        };
        // Binder B will be before binder A because it isn't dependent on another binder's bindings
        $expectedCollection = new BinderMetadataCollection([
            new BinderMetadata($binderB, [new BoundInterface(IFoo::class, new UniversalContext())], []),
            new BinderMetadata($binderC, [new BoundInterface(IBar::class, new UniversalContext())], []),
            new BinderMetadata($binderA, [new BoundInterface(IPerson::class, new UniversalContext())], [new ResolvedInterface(IFoo::class, new UniversalContext()), new ResolvedInterface(IBar::class, new UniversalContext())])
        ]);
        $actualCollection = $this->factory->createBinderMetadataCollection([$binderA, $binderB, $binderC]);
        $this->assertEquals($expectedCollection, $actualCollection);
    }

    public function testCreatingCollectionWithBinderThatCannotResolveSomethingThrowsException(): void
    {
        $this->expectException(ImpossibleBindingException::class);
        $binder = new class () extends Binder {
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
        $binderA = new class () extends Binder {
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
        $binderB = new class () extends Binder {
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
