<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/configuration/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Configuration\Tests;

use Aphiria\Configuration\AphiriaComponentBuilder;
use Aphiria\Configuration\IApplicationBuilder;
use Aphiria\Console\Commands\CommandRegistry;
use Opulence\Ioc\IContainer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the Aphiria component builder
 */
class AphiriaComponentBuilderTest extends TestCase
{
    /** @var IContainer|MockObject */
    private IContainer $container;
    private AphiriaComponentBuilder $componentBuilder;
    /** @var IApplicationBuilder|MockObject */
    private IApplicationBuilder $appBuilder;

    protected function setUp(): void
    {
        $this->appBuilder = $this->createMock(IApplicationBuilder::class);
        $this->container = $this->createMock(IContainer::class);
        $this->componentBuilder = new AphiriaComponentBuilder($this->container);
    }

    public function testWithCommandComponentPassesCommandRegistryToRegisteredCallbacks(): void
    {
        $this->appBuilder->expects($this->once())
            ->method('registerComponentFactory')
            ->with('commands', $this->callback(function (\Closure $callback) {
                $callbackWasCalled = false;
                $callbacks = [
                    function (CommandRegistry $commands) use (&$callbackWasCalled) {
                        $callbackWasCalled = true;
                    }
                ];
                // Call the callback so we can verify it was setup correctly
                $callback($callbacks);

                return $callbackWasCalled;
            }));
        $this->componentBuilder->withCommandComponent($this->appBuilder);
    }

    public function testWithRoutingComponentRegistersRouter(): void
    {
        $this->appBuilder->expects($this->once())
            ->method('withRouter');
        $this->componentBuilder->withRoutingComponent($this->appBuilder);
    }
}
