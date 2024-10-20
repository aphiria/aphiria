<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Middleware\Components;

use Aphiria\DependencyInjection\IServiceResolver;
use Aphiria\Framework\Middleware\Components\MiddlewareComponent;
use Aphiria\Middleware\IMiddleware;
use Aphiria\Middleware\MiddlewareBinding;
use Aphiria\Middleware\MiddlewareCollection;
use Aphiria\Middleware\ParameterizedMiddleware;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponse;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MiddlewareComponentTest extends TestCase
{
    private IServiceResolver&MockObject $dependencyResolver;
    private MiddlewareComponent $middlewareComponent;

    protected function setUp(): void
    {
        // Using a real container to simplify testing
        $this->dependencyResolver = $this->createMock(IServiceResolver::class);
        $this->middlewareComponent = new MiddlewareComponent($this->dependencyResolver);
    }

    public function testBuildWithAttributeMiddlewareSetsAttributes(): void
    {
        $expectedMiddleware = new class ($this->createMock(IResponse::class)) extends ParameterizedMiddleware {
            private IResponse $expectedResponse;

            public function __construct(IResponse $expectedResponse)
            {
                $this->expectedResponse = $expectedResponse;
            }

            public function handle(IRequest $request, IRequestHandler $next): IResponse
            {
                return $this->expectedResponse;
            }
        };
        $expectedMiddleware->parameters = ['bar' => 'baz'];
        $middlewareCollection = new MiddlewareCollection();
        $this->dependencyResolver->method('resolve')
            ->willReturnMap([
                [MiddlewareCollection::class, $middlewareCollection],
                [$expectedMiddleware::class, $expectedMiddleware]
            ]);
        $this->middlewareComponent->withGlobalMiddleware(new MiddlewareBinding($expectedMiddleware::class, ['bar' => 'baz']));
        $this->middlewareComponent->build();
        $this->assertEquals([$expectedMiddleware], $middlewareCollection->values);
    }

    public function testBuildWithMiddlewareThatIsNotCorrectInterfaceThrowsException(): void
    {
        $invalidMiddleware = $this;
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($invalidMiddleware::class . ' does not implement ' . IMiddleware::class);
        $this->dependencyResolver->method('resolve')
            ->willReturnMap([
                [MiddlewareCollection::class, new MiddlewareCollection()],
                [$invalidMiddleware::class, $invalidMiddleware]
            ]);
        $this->middlewareComponent->withGlobalMiddleware(new MiddlewareBinding($invalidMiddleware::class));
        $this->middlewareComponent->build();
    }

    public function testWithGlobalMiddlewareAppendsItToCollectionToBeResolved(): void
    {
        $expectedMiddleware1 = $this->createMock(IMiddleware::class);
        $expectedMiddleware2 = $this->createMock(IMiddleware::class);
        $middlewareCollection = new MiddlewareCollection();
        $this->dependencyResolver->method('resolve')
            ->willReturnMap([
                [MiddlewareCollection::class, $middlewareCollection],
                [$expectedMiddleware1::class, $expectedMiddleware1],
                [$expectedMiddleware2::class, $expectedMiddleware2]
            ]);
        $this->middlewareComponent->withGlobalMiddleware(new MiddlewareBinding($expectedMiddleware1::class));
        $this->middlewareComponent->withGlobalMiddleware(new MiddlewareBinding($expectedMiddleware2::class));
        $this->middlewareComponent->build();
        $this->assertEquals([$expectedMiddleware1, $expectedMiddleware2], $middlewareCollection->values);
    }

    public function testWithMultipleGlobalMiddlewareAddsThemToCollectionToBeResolved(): void
    {
        $expectedMiddleware1 = $this->createMock(IMiddleware::class);
        $expectedMiddleware2 = $this->createMock(IMiddleware::class);
        $middlewareCollection = new MiddlewareCollection();
        $this->dependencyResolver->method('resolve')
            ->willReturnMap([
                [MiddlewareCollection::class, $middlewareCollection],
                [$expectedMiddleware1::class, $expectedMiddleware1],
                [$expectedMiddleware2::class, $expectedMiddleware2]
            ]);
        $this->middlewareComponent->withGlobalMiddleware([
            new MiddlewareBinding($expectedMiddleware1::class), new MiddlewareBinding($expectedMiddleware2::class)
        ]);
        $this->middlewareComponent->build();
        $this->assertEquals([$expectedMiddleware1, $expectedMiddleware2], $middlewareCollection->values);
    }

    public function testWithSingleGlobalMiddlewareAddsItToCollectionToBeResolved(): void
    {
        $expectedMiddleware = $this->createMock(IMiddleware::class);
        $middlewareCollection = new MiddlewareCollection();
        $this->dependencyResolver->method('resolve')
            ->willReturnMap([
                [MiddlewareCollection::class, $middlewareCollection],
                [$expectedMiddleware::class, $expectedMiddleware]
            ]);
        $this->middlewareComponent->withGlobalMiddleware(new MiddlewareBinding($expectedMiddleware::class));
        $this->middlewareComponent->build();
        $this->assertEquals([$expectedMiddleware], $middlewareCollection->values);
    }
}
