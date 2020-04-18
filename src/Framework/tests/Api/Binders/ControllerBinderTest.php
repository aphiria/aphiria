<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Api\Binders;

use Aphiria\Api\Controllers\IRouteActionInvoker;
use Aphiria\Api\Controllers\RouteActionInvoker;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Api\Binders\ControllerBinder;
use Aphiria\Net\Http\ContentNegotiation\IContentNegotiator;
use Aphiria\Net\Http\IResponseFactory;
use Aphiria\Validation\ErrorMessages\IErrorMessageInterpolator;
use Aphiria\Validation\IValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ControllerBinderTest extends TestCase
{
    /** @var IContainer|MockObject */
    private IContainer $container;
    private ControllerBinder $binder;

    protected function setUp(): void
    {
        $this->container = $this->createMock(IContainer::class);
        $this->binder = new ControllerBinder();

        // Set up some universal mocks
        $this->container->expects($this->at(0))
            ->method('resolve')
            ->with(IValidator::class)
            ->willReturn($this->createMock(IValidator::class));
        $this->container->expects($this->at(1))
            ->method('resolve')
            ->with(IErrorMessageInterpolator::class)
            ->willReturn($this->createMock(IErrorMessageInterpolator::class));
        $this->container->expects($this->at(2))
            ->method('resolve')
            ->with(IContentNegotiator::class)
            ->willReturn($this->createMock(IContentNegotiator::class));
        $this->container->expects($this->at(3))
            ->method('resolve')
            ->with(IContentNegotiator::class)
            ->willReturn($this->createMock(IContentNegotiator::class));
        $this->container->expects($this->at(4))
            ->method('resolve')
            ->with(IResponseFactory::class)
            ->willReturn($this->createMock(IResponseFactory::class));
    }

    public function testRouteActionInvokerIsBound(): void
    {
        $this->container->expects($this->at(5))
            ->method('bindInstance')
            ->with(IRouteActionInvoker::class, $this->isInstanceOf(RouteActionInvoker::class));
        $this->binder->bind($this->container);
    }
}
