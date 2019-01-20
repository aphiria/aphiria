<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Tests\Middleware;

use InvalidArgumentException;
use Opulence\Api\IDependencyResolver;
use Opulence\Api\Middleware\MiddlewareRequestHandlerResolver;
use Opulence\Api\Tests\Middleware\Mocks\AttributeMiddleware;
use Opulence\Net\Http\Handlers\IRequestHandler;
use Opulence\Routing\Middleware\MiddlewareBinding;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the middleware request handler resolver
 */
class MiddlewareRequestHandlerResolverTest extends TestCase
{
    /** @var IDependencyResolver|MockObject */
    private $dependencyResolver;
    /** @var MiddlewareRequestHandlerResolver */
    private  $middlewareRequestHandlerResolver;

    public function setUp(): void
    {
        $this->dependencyResolver = $this->createMock(IDependencyResolver::class);
        $this->middlewareRequestHandlerResolver = new MiddlewareRequestHandlerResolver($this->dependencyResolver);
    }

    public function testAttributeMiddlewareIsResolvedAndAttributesAreSet(): void
    {
        $middleware = new AttributeMiddleware();
        $this->dependencyResolver->expects($this->once())
            ->method('resolve')
            ->with(AttributeMiddleware::class)
            ->willReturn($middleware);
        $middlewareBinding = new MiddlewareBinding(AttributeMiddleware::class, ['foo' => 'bar']);
        /** @var IRequestHandler|MockObject $next */
        $next = $this->createMock(IRequestHandler::class);
        $this->middlewareRequestHandlerResolver->resolve($middlewareBinding, $next);
        // Test that the middleware actually set the headers
        $this->assertEquals('bar', $middleware->getAttribute('foo'));
    }

    public function testInvalidMiddlewareThrowsExceptionThatIsCaught(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $middleware = $this;
        $this->dependencyResolver->expects($this->once())
            ->method('resolve')
            ->with(__CLASS__)
            ->willReturn($middleware);
        $middlewareBinding = new MiddlewareBinding(__CLASS__);
        /** @var IRequestHandler|MockObject $next */
        $next = $this->createMock(IRequestHandler::class);
        $this->middlewareRequestHandlerResolver->resolve($middlewareBinding, $next);
    }
}
