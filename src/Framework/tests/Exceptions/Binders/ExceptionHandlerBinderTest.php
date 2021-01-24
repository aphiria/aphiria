<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Exceptions\Binders;

use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Api\Exceptions\IApiExceptionRenderer;
use Aphiria\Framework\Api\Exceptions\ProblemDetailsExceptionRenderer;
use Aphiria\Framework\Exceptions\Binders\ExceptionHandlerBinder;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponseFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExceptionHandlerBinderTest extends TestCase
{
    private IContainer|MockObject $container;
    private ExceptionHandlerBinder $binder;
    private ?IApiExceptionRenderer $apiExceptionRenderer;
    private ?IRequest $request;
    private ?IResponseFactory $responseFactory;

    protected function setUp(): void
    {
        $this->container = $this->createMock(IContainer::class);
        $this->binder = new ExceptionHandlerBinder();
        $this->apiExceptionRenderer = $this->request = $this->responseFactory = null;

        // We need to set up the "out" params based on the type that's set
        $this->container->method('tryResolve')
            ->willReturnCallback(function (string $type, ?object &$object) {
                if ($type === IApiExceptionRenderer::class) {
                    // Capture the object parameter so we can make assertions on it later
                    $object = $this->apiExceptionRenderer = new class() extends ProblemDetailsExceptionRenderer {
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
                }

                if ($type === IRequest::class) {
                    // Capture the object parameter so we can make assertions on it later
                    $object = $this->request = $this->createMock(IRequest::class);

                    return true;
                }

                if ($type === IResponseFactory::class) {
                    // Capture the object parameter so we can make assertions on it later
                    $object = $this->responseFactory = $this->createMock(IResponseFactory::class);

                    return true;
                }

                return false;
            });
    }

    public function testRequestIsSetOnApiExceptionRenderer(): void
    {
        $this->binder->bind($this->container);
        $this->assertSame($this->request, $this->apiExceptionRenderer?->getRequest());
    }

    public function testResponseFactoryIsSetOnApiExceptionRenderer(): void
    {
        $this->binder->bind($this->container);
        $this->assertSame($this->responseFactory, $this->apiExceptionRenderer?->getResponseFactory());
    }
}
