<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\DependencyInjection\Components;

use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\Binders\IBinderDispatcher;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\ReflectionContainer;
use Aphiria\Framework\DependencyInjection\Components\BinderComponent;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class BinderComponentTest extends TestCase
{
    private BinderComponent $binderComponent;

    protected function setUp(): void
    {
        $this->binderComponent = new BinderComponent(new ReflectionContainer());
    }

    public function testBuildWithBindersAppendsToListOfBindersToBeDispatched(): void
    {
        $binder1 = new class () extends Binder {
            public function bind(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $binder2 = new class () extends Binder {
            public function bind(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $binderDispatcher = $this->createMock(IBinderDispatcher::class);
        $binderDispatcher->expects($this->once())
            ->method('dispatch')
            ->with([$binder1, $binder2]);
        $this->binderComponent->withBinderDispatcher($binderDispatcher);
        $this->binderComponent->withBinders($binder1);
        $this->binderComponent->withBinders($binder2);
        $this->binderComponent->build();
    }

    public function testBuildWithBindersWithMultipleBinderAddsThemToBindersToBeDispatched(): void
    {
        $binder1 = new class () extends Binder {
            public function bind(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $binder2 = new class () extends Binder {
            public function bind(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $binderDispatcher = $this->createMock(IBinderDispatcher::class);
        $binderDispatcher->expects($this->once())
            ->method('dispatch')
            ->with([$binder1, $binder2]);
        $this->binderComponent->withBinderDispatcher($binderDispatcher);
        $this->binderComponent->withBinders([$binder1, $binder2]);
        $this->binderComponent->build();
    }

    public function testBuildWithBindersWithSingleBinderAddsItToBindersToBeDispatched(): void
    {
        $binder = new class () extends Binder {
            public function bind(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $binderDispatcher = $this->createMock(IBinderDispatcher::class);
        $binderDispatcher->expects($this->once())
            ->method('dispatch')
            ->with([$binder]);
        $this->binderComponent->withBinderDispatcher($binderDispatcher);
        $this->binderComponent->withBinders($binder);
        $this->binderComponent->build();
    }

    public function testBuildingWithoutBinderDispatcherThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Must call withBinderDispatcher() before building');
        $this->binderComponent->build();
    }
}
