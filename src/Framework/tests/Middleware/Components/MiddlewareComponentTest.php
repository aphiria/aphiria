<?php
/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Middleware\Components;

use Aphiria\DependencyInjection\IServiceResolver;
use Aphiria\Framework\Middleware\Components\MiddlewareComponent;
use Aphiria\Middleware\AttributeMiddleware;
use Aphiria\Middleware\IMiddleware;
use Aphiria\Middleware\MiddlewareBinding;
use Aphiria\Middleware\MiddlewareCollection;
use Aphiria\Net\Http\Handlers\IRequestHandler;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IHttpResponseMessage;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the middleware component
 */
class MiddlewareComponentTest extends TestCase
{
    private MiddlewareComponent $middlewareComponent;
    /** @var IServiceResolver|MockObject  */
    private IServiceResolver $dependencyResolver;

    protected function setUp(): void
    {
        // Using a real container to simplify testing
        $this->dependencyResolver = $this->createMock(IServiceResolver::class);
        $this->middlewareComponent = new MiddlewareComponent($this->dependencyResolver);
    }

    public function testInitializeWithAttributeMiddlewareSetsAttributes(): void
    {
        $expectedMiddleware = new class($this->createMock(IHttpResponseMessage::class)) extends AttributeMiddleware
        {
            private IHttpResponseMessage $expectedResponse;

            public function __construct(IHttpResponseMessage $expectedResponse)
            {
                $this->expectedResponse = $expectedResponse;
            }

            public function handle(IHttpRequestMessage $request, IRequestHandler $next): IHttpResponseMessage
            {
                return $this->expectedResponse;
            }
        };
        $expectedMiddleware->setAttributes(['bar' => 'baz']);
        $middlewareCollection = new MiddlewareCollection();
        $this->dependencyResolver->expects($this->at(0))
            ->method('resolve')
            ->with(MiddlewareCollection::class)
            ->willReturn($middlewareCollection);
        $this->dependencyResolver->expects($this->at(1))
            ->method('resolve')
            ->with('foo')
            ->willReturn($expectedMiddleware);
        $this->middlewareComponent->withGlobalMiddleware(new MiddlewareBinding('foo', ['bar' => 'baz']));
        $this->middlewareComponent->initialize();
        $this->assertEquals([$expectedMiddleware], $middlewareCollection->getAll());
    }

    public function testInitializeWithMiddlewareThatIsNotCorrectInterfaceThrowsException(): void
    {
        $invalidMiddleware = $this;
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\get_class($invalidMiddleware) . ' does not implement ' . IMiddleware::class);
        $this->dependencyResolver->expects($this->at(1))
            ->method('resolve')
            ->with('foo')
            ->willReturn($invalidMiddleware);
        $this->middlewareComponent->withGlobalMiddleware(new MiddlewareBinding('foo'));
        $this->middlewareComponent->initialize();
    }

    public function testWithGlobalMiddlewareAppendsItToCollectionToBeResolved(): void
    {
        $expectedMiddleware1 = $this->createMock(IMiddleware::class);
        $expectedMiddleware2 = $this->createMock(IMiddleware::class);
        $middlewareCollection = new MiddlewareCollection();
        $this->dependencyResolver->expects($this->at(0))
            ->method('resolve')
            ->with(MiddlewareCollection::class)
            ->willReturn($middlewareCollection);
        $this->dependencyResolver->expects($this->at(1))
            ->method('resolve')
            ->with('foo')
            ->willReturn($expectedMiddleware1);
        $this->dependencyResolver->expects($this->at(2))
            ->method('resolve')
            ->with('bar')
            ->willReturn($expectedMiddleware2);
        $this->middlewareComponent->withGlobalMiddleware(new MiddlewareBinding('foo'));
        $this->middlewareComponent->withGlobalMiddleware(new MiddlewareBinding('bar'));
        $this->middlewareComponent->initialize();
        $this->assertEquals([$expectedMiddleware1, $expectedMiddleware2], $middlewareCollection->getAll());
    }

    public function testWithMultipleGlobalMiddlewareAddsThemToCollectionToBeResolved(): void
    {
        $expectedMiddleware1 = $this->createMock(IMiddleware::class);
        $expectedMiddleware2 = $this->createMock(IMiddleware::class);
        $middlewareCollection = new MiddlewareCollection();
        $this->dependencyResolver->expects($this->at(0))
            ->method('resolve')
            ->with(MiddlewareCollection::class)
            ->willReturn($middlewareCollection);
        $this->dependencyResolver->expects($this->at(1))
            ->method('resolve')
            ->with('foo')
            ->willReturn($expectedMiddleware1);
        $this->dependencyResolver->expects($this->at(2))
            ->method('resolve')
            ->with('bar')
            ->willReturn($expectedMiddleware2);
        $this->middlewareComponent->withGlobalMiddleware([new MiddlewareBinding('foo'), new MiddlewareBinding('bar')]);
        $this->middlewareComponent->initialize();
        $this->assertEquals([$expectedMiddleware1, $expectedMiddleware2], $middlewareCollection->getAll());
    }

    public function testWithSingleGlobalMiddlewareAddsItToCollectionToBeResolved(): void
    {
        $expectedMiddleware = $this->createMock(IMiddleware::class);
        $middlewareCollection = new MiddlewareCollection();
        $this->dependencyResolver->expects($this->at(0))
            ->method('resolve')
            ->with(MiddlewareCollection::class)
            ->willReturn($middlewareCollection);
        $this->dependencyResolver->expects($this->at(1))
            ->method('resolve')
            ->with('foo')
            ->willReturn($expectedMiddleware);
        $this->middlewareComponent->withGlobalMiddleware(new MiddlewareBinding('foo'));
        $this->middlewareComponent->initialize();
        $this->assertEquals([$expectedMiddleware], $middlewareCollection->getAll());
    }
}
