<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Configuration\Tests;

use Aphiria\Configuration\AphiriaComponentBuilder;
use Aphiria\Configuration\IApplicationBuilder;
use Aphiria\Exceptions\ExceptionLogLevelFactoryRegistry;
use Aphiria\Exceptions\ExceptionResponseFactoryRegistry;
use Aphiria\Serialization\Encoding\EncoderRegistry;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Validation\ConstraintRegistry;
use Closure;
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

    public function testWithConsoleAnnotationsRegistersComponent(): void
    {
        $this->appBuilder->expects($this->at(0))
            ->method('registerComponentBuilder')
            ->with('consoleAnnotations');
        $this->componentBuilder->withConsoleAnnotations($this->appBuilder);
    }

    public function testWithEncoderComponentPassesEncoderRegistryToRegisteredCallbacks(): void
    {
        $this->appBuilder->expects($this->once())
            ->method('registerComponentBuilder')
            ->with('encoders', $this->callback(function (Closure $callback) {
                $callbackWasCalled = false;
                $callbacks = [
                    function (EncoderRegistry $encoders) use (&$callbackWasCalled) {
                        $callbackWasCalled = true;
                    }
                ];
                // Call the callback so we can verify it was setup correctly
                $callback($callbacks);

                return $callbackWasCalled;
            }));
        $this->componentBuilder->withEncoderComponent($this->appBuilder);
    }

    public function testWithExceptionHandlingRegistersComponent(): void
    {
        // The first 2 invocations will be to register the routing component
        $this->appBuilder->expects($this->once())
            ->method('registerComponentBuilder')
            ->with('exceptionHandlers');
        $this->componentBuilder->withExceptionHandlers($this->appBuilder);
    }

    public function testWithExceptionLogLevelFactoriesComponentPassesRegistryToRegisteredCallbacks(): void
    {
        $this->appBuilder->expects($this->once())
            ->method('registerComponentBuilder')
            ->with('exceptionLogLevelFactories', $this->callback(function (Closure $callback) {
                $callbackWasCalled = false;
                $callbacks = [
                    function (ExceptionLogLevelFactoryRegistry $factories) use (&$callbackWasCalled) {
                        $callbackWasCalled = true;
                    }
                ];
                // Call the callback so we can verify it was setup correctly
                $callback($callbacks);

                return $callbackWasCalled;
            }));
        $this->componentBuilder->withExceptionLogLevelFactories($this->appBuilder);
    }

    public function testWithExceptionResponseFactoriesComponentPassesRegistryToRegisteredCallbacks(): void
    {
        $this->appBuilder->expects($this->once())
            ->method('registerComponentBuilder')
            ->with('exceptionResponseFactories', $this->callback(function (Closure $callback) {
                $callbackWasCalled = false;
                $callbacks = [
                    function (ExceptionResponseFactoryRegistry $factories) use (&$callbackWasCalled) {
                        $callbackWasCalled = true;
                    }
                ];
                // Call the callback so we can verify it was setup correctly
                $callback($callbacks);

                return $callbackWasCalled;
            }));
        $this->componentBuilder->withExceptionResponseFactories($this->appBuilder);
    }

    public function testWithRouteAnnotationsRegistersComponent(): void
    {
        // The first 2 invocations will be to register the routing component
        $this->appBuilder->expects($this->at(2))
            ->method('registerComponentBuilder')
            ->with('routeAnnotations');
        $this->componentBuilder->withRoutingComponent($this->appBuilder);
        $this->componentBuilder->withRouteAnnotations($this->appBuilder);
    }

    public function testWithRouteAnnotationsWithoutRoutingComponentThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Routing component must be enabled via withRoutingComponent() to use route annotations');
        $this->componentBuilder->withRouteAnnotations($this->appBuilder);
    }

    public function testWithRoutingComponentRegistersRouter(): void
    {
        $this->appBuilder->expects($this->at(0))
            ->method('withRouter');
        $this->appBuilder->expects($this->at(1))
            ->method('registerComponentBuilder')
            ->with('routes');
        $this->componentBuilder->withRoutingComponent($this->appBuilder);
    }

    public function testWithValidationComponentPassesConstraintRegistryToRegisteredCallbacks(): void
    {
        $this->appBuilder->expects($this->once())
            ->method('registerComponentBuilder')
            ->with('validators', $this->callback(function (Closure $callback) {
                $callbackWasCalled = false;
                $callbacks = [
                    function (ConstraintRegistry $constraints) use (&$callbackWasCalled) {
                        $callbackWasCalled = true;
                    }
                ];
                // Call the callback so we can verify it was setup correctly
                $callback($callbacks);

                return $callbackWasCalled;
            }));
        $this->componentBuilder->withValidationComponent($this->appBuilder);
    }
}
