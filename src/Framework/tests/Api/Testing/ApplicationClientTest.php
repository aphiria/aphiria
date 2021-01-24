<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Api\Testing;

use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Api\Testing\ApplicationClient;
use Aphiria\Framework\Net\Binders\RequestBinder;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class ApplicationClientTest extends TestCase
{
    private IRequestHandler|MockObject $app;
    private IContainer|MockObject $container;
    private ApplicationClient $appClient;

    protected function setUp(): void
    {
        $this->app = $this->createMock(IRequestHandler::class);
        $this->container = $this->createMock(IContainer::class);
        $this->appClient = new ApplicationClient($this->app, $this->container);
    }

    public function testSendOverridesRequestBinderRequestAndInvokesApp(): void
    {
        $response = $this->createMock(IResponse::class);
        $request = $this->createMock(IRequest::class);
        $this->container->expects($this->once())
            ->method('resolve')
            ->with(IRequest::class)
            ->willReturn($request);
        $this->app->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);
        $this->assertSame($response, $this->appClient->send($request));
        $reflectionProperty = new ReflectionProperty(RequestBinder::class, 'overridingRequest');
        $reflectionProperty->setAccessible(true);
        $this->assertSame($request, $reflectionProperty->getValue());
    }

    public function testSendRebindsRequestIfResolvedRequestDoeNotMatchInputRequest(): void
    {
        $response = $this->createMock(IResponse::class);
        $request = $this->createMock(IRequest::class);
        $this->container->method('resolve')
            ->with(IRequest::class)
            ->willReturn($this->createMock(IRequest::class));
        $this->container->method('bindInstance')
            ->with(IRequest::class, $request);
        $this->app->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);
        $this->assertSame($response, $this->appClient->send($request));
    }
}
