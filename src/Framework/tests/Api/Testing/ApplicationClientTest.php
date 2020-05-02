<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Api\Testing;

use Aphiria\DependencyInjection\IServiceResolver;
use Aphiria\Framework\Api\Testing\ApplicationClient;
use Aphiria\Framework\Net\Binders\RequestBinder;
use Aphiria\Net\Http\Handlers\IRequestHandler;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class ApplicationClientTest extends TestCase
{
    /** @var IRequestHandler|MockObject */
    private IRequestHandler $app;
    /** @var IServiceResolver|MockObject */
    private IServiceResolver $serviceResolver;
    private ApplicationClient $appClient;

    protected function setUp(): void
    {
        $this->app = $this->createMock(IRequestHandler::class);
        $this->serviceResolver = $this->createMock(IServiceResolver::class);
        $this->appClient = new ApplicationClient($this->app, $this->serviceResolver);
    }

    public function testSendOverridesRequestBinderRequestAndInvokesApp(): void
    {
        $response = $this->createMock(IResponse::class);
        $request = $this->createMock(IRequest::class);
        $this->serviceResolver->expects($this->once())
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
}
