<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Api\Binders;

use Aphiria\Api\Controllers\IRouteActionInvoker;
use Aphiria\Api\Controllers\RouteActionInvoker;
use Aphiria\ContentNegotiation\IContentNegotiator;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Api\Binders\ControllerBinder;
use Aphiria\Net\Http\IResponseFactory;
use Aphiria\Validation\ErrorMessages\IErrorMessageInterpolator;
use Aphiria\Validation\IValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ControllerBinderTest extends TestCase
{
    private IContainer|MockObject $container;
    private ControllerBinder $binder;

    protected function setUp(): void
    {
        $this->container = $this->createMock(IContainer::class);
        $this->binder = new ControllerBinder();

        // Set up some universal mocks
        $this->container->method('resolve')
            ->willReturnMap([
                [IValidator::class, $this->createMock(IValidator::class)],
                [IErrorMessageInterpolator::class, $this->createMock(IErrorMessageInterpolator::class)],
                [IContentNegotiator::class, $this->createMock(IContentNegotiator::class)],
                [IResponseFactory::class, $this->createMock(IResponseFactory::class)]
            ]);
    }

    public function testRouteActionInvokerIsBound(): void
    {
        $this->container->method('bindInstance')
            ->with(IRouteActionInvoker::class, $this->isInstanceOf(RouteActionInvoker::class));
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }
}
