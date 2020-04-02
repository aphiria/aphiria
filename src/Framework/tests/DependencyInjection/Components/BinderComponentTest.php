<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\DependencyInjection\Components;

use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\Binders\IBinderDispatcher;
use Aphiria\DependencyInjection\Container;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\DependencyInjection\Components\BinderComponent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the binder component
 */
class BinderComponentTest extends TestCase
{
    private BinderComponent $binderComponent;
    /** @var IBinderDispatcher|MockObject */
    private IBinderDispatcher $binderDispatcher;

    protected function setUp(): void
    {
        $this->binderDispatcher = $this->createMock(IBinderDispatcher::class);
        $this->binderComponent = new BinderComponent($this->binderDispatcher, new Container());
    }

    public function testBuildWithBindersAppendsToListOfBindersToBeDispatched(): void
    {
        $binder1 = new class() extends Binder {
            public function bind(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $binder2 = new class() extends Binder {
            public function bind(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $this->binderDispatcher->expects($this->once())
            ->method('dispatch')
            ->with([$binder1, $binder2]);
        $this->binderComponent->withBinders($binder1);
        $this->binderComponent->withBinders($binder2);
        $this->binderComponent->build();
    }

    public function testBuildWithBindersWithMultipleBinderAddsThemToBindersToBeDispatched(): void
    {
        $binder1 = new class() extends Binder {
            public function bind(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $binder2 = new class() extends Binder {
            public function bind(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $this->binderDispatcher->expects($this->once())
            ->method('dispatch')
            ->with([$binder1, $binder2]);
        $this->binderComponent->withBinders([$binder1, $binder2]);
        $this->binderComponent->build();
    }

    public function testBuildWithBindersWithSingleBinderAddsItToBindersToBeDispatched(): void
    {
        $binder = new class() extends Binder {
            public function bind(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $this->binderDispatcher->expects($this->once())
            ->method('dispatch')
            ->with([$binder]);
        $this->binderComponent->withBinders($binder);
        $this->binderComponent->build();
    }
}
