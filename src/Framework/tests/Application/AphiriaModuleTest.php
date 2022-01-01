<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Application;

use Aphiria\Application\Builders\IApplicationBuilder;
use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Application\AphiriaModule;
use Aphiria\Framework\DependencyInjection\Components\BinderComponent;
use PHPUnit\Framework\TestCase;

class AphiriaModuleTest extends TestCase
{
    public function testAphiriaComponentsCanBeRegisteredOnAphiriaModule(): void
    {
        $expectedBinder = new class () extends Binder {
            public function bind(IContainer $container): void
            {
                return;
            }
        };
        $binderComponent = new class ($this->createMock(IContainer::class)) extends BinderComponent {
            /** @var list<Binder> */
            public array $binders = [];

            public function withBinders(array|Binder $binders): static
            {
                $binders = \is_array($binders) ? $binders : [$binders];
                $this->binders = [...$this->binders, ...$binders];

                return parent::withBinders($binders);
            }
        };
        $module = new class ($expectedBinder) extends AphiriaModule {
            public function __construct(private Binder $expectedBinder)
            {
            }

            public function configure(IApplicationBuilder $appBuilder): void
            {
                $this->withBinders($appBuilder, $this->expectedBinder);
            }
        };
        $appBuilder = $this->createMock(IApplicationBuilder::class);
        $appBuilder->method('hasComponent')
            ->with(BinderComponent::class)
            ->willReturn(true);
        $appBuilder->method('getComponent')
            ->with(BinderComponent::class)
            ->willReturn($binderComponent);
        $module->configure($appBuilder);
        $this->assertSame([$expectedBinder], $binderComponent->binders);
    }
}
