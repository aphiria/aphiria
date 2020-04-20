<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Exceptions\Binders;

use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Api\Exceptions\ApiExceptionRenderer;
use Aphiria\Framework\Exceptions\Binders\ExceptionHandlerBinder;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponseFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExceptionHandlerBinderTest extends TestCase
{
    /** @var IContainer|MockObject */
    private IContainer $container;
    private ExceptionHandlerBinder $binder;
    private ?ApiExceptionRenderer $apiExceptionRenderer;
    private ?IRequest $request;
    private ?IResponseFactory $responseFactory;

    protected function setUp(): void
    {
        $this->container = $this->createMock(IContainer::class);
        $this->binder = new ExceptionHandlerBinder();
        $this->apiExceptionRenderer = $this->request = $this->responseFactory = null;

        // Set up some universal mocks
        $this->container->expects($this->at(0))
            ->method('tryResolve')
            ->with(ApiExceptionRenderer::class, $this->apiExceptionRenderer)
            ->willReturnCallback(function ($type, &$object) {
                // Capture the object parameter so we can make assertions on it later
                $object = $this->apiExceptionRenderer = new class() extends ApiExceptionRenderer {
                    public function getRequest(): ?IRequest
                    {
                        return $this->request;
                    }

                    public function getResponseFactory(): ?IResponseFactory
                    {
                        return $this->responseFactory;
                    }
                };

                return true;
            });
        $this->container->expects($this->at(1))
            ->method('tryResolve')
            ->with(IRequest::class, $this->request)
            ->willReturnCallback(function ($type, &$object) {
                // Capture the object parameter so we can make assertions on it later
                $object = $this->request = $this->createMock(IRequest::class);

                return true;
            });
        $this->container->expects($this->at(2))
            ->method('tryResolve')
            ->with(IResponseFactory::class, $this->responseFactory)
            ->willReturnCallback(function ($type, &$object) {
                // Capture the object parameter so we can make assertions on it later
                $object = $this->responseFactory = $this->createMock(IResponseFactory::class);

                return true;
            });
    }

    public function testRequestIsSetOnApiExceptionRenderer(): void
    {
        $this->binder->bind($this->container);
        $this->assertSame($this->request, $this->apiExceptionRenderer->getRequest());
    }

    public function testResponseFactoryIsSetOnApiExceptionRenderer(): void
    {
        $this->binder->bind($this->container);
        $this->assertSame($this->responseFactory, $this->apiExceptionRenderer->getResponseFactory());
    }
}
