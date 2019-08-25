<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Tests;

use Aphiria\Api\App;
use Aphiria\Api\IDependencyResolver;
use Aphiria\Api\Tests\Mocks\AttributeMiddleware;
use Aphiria\Middleware\IMiddleware;
use Aphiria\Net\Http\Handlers\IRequestHandler;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IHttpResponseMessage;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function get_class;

/**
 * Tests the app
 */
class AppTest extends TestCase
{
    private App $app;
    /** @var IDependencyResolver|MockObject */
    private IDependencyResolver $dependencyResolver;
    /** @var IRequestHandler|MockObject */
    private IRequestHandler $kernel;

    protected function setUp(): void
    {
        $this->dependencyResolver = $this->createMock(IDependencyResolver::class);
        $this->kernel = $this->createMock(IRequestHandler::class);
        $this->app = new App($this->dependencyResolver, $this->kernel);
    }

    public function testAddingAttributeMiddlewareSetsAttributesOnIt(): void
    {
        $middleware = new class() extends AttributeMiddleware {
            public function handle(IHttpRequestMessage $request, IRequestHandler $next): IHttpResponseMessage
            {
                return $next->handle($request);
            }
        };
        $this->dependencyResolver->expects($this->once())
            ->method('resolve')
            ->with(get_class($middleware))
            ->willReturn($middleware);
        $this->app->addMiddleware(get_class($middleware), ['foo' => 'bar']);
        $this->assertEquals('bar', $middleware->getAttribute('foo'));
    }

    public function testAddingInvalidMiddlewareThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->dependencyResolver->expects($this->once())
            ->method('resolve')
            ->with(self::class)
            ->willReturn($this);
        $this->app->addMiddleware(self::class);
    }

    public function testAddingMiddlewareSendsRequestThroughIt(): void
    {
        /** @var IHttpRequestMessage|MockObject $request */
        $request = $this->createMock(IHttpRequestMessage::class);
        /** @var IHttpResponseMessage|MockObject $response */
        $response = $this->createMock(IHttpResponseMessage::class);
        $this->kernel->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);
        $middleware = new class() implements IMiddleware {
            public $wasCalled = false;

            public function handle(IHttpRequestMessage $request, IRequestHandler $next): IHttpResponseMessage
            {
                $this->wasCalled = true;

                return $next->handle($request);
            }
        };
        $this->dependencyResolver->expects($this->once())
            ->method('resolve')
            ->with(get_class($middleware))
            ->willReturn($middleware);
        $this->app->addMiddleware(get_class($middleware));
        $this->assertSame($response, $this->app->handle($request));
        $this->assertTrue($middleware->wasCalled);
    }
}
