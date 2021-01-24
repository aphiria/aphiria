<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Middleware\Tests;

use Aphiria\Middleware\IMiddleware;
use Aphiria\Middleware\MiddlewareCollection;
use PHPUnit\Framework\TestCase;

class MiddlewareCollectionTest extends TestCase
{
    private MiddlewareCollection $middlewareCollection;

    protected function setUp(): void
    {
        $this->middlewareCollection = new MiddlewareCollection();
    }

    public function testAddingMultipleMiddlewareShowsUpInGetAll(): void
    {
        $middleware1 = $this->createMock(IMiddleware::class);
        $middleware2 = $this->createMock(IMiddleware::class);
        $this->middlewareCollection->add($middleware1);
        $this->middlewareCollection->add($middleware2);
        $this->assertSame([$middleware1, $middleware2], $this->middlewareCollection->getAll());
    }

    public function testAddingPriorityMiddlewareCausesItToBeOrderedBeforePreviouslyAddedMiddleware(): void
    {
        $unprioritizedMiddleware = $this->createMock(IMiddleware::class);
        $prioritizedMiddleware = $this->createMock(IMiddleware::class);
        $this->middlewareCollection->add($unprioritizedMiddleware);
        $this->middlewareCollection->add($prioritizedMiddleware, 1);
        $this->assertSame([$prioritizedMiddleware, $unprioritizedMiddleware], $this->middlewareCollection->getAll());
    }

    public function testAddingSamePriorityMiddlewareCausesThemToBeReturnedInOrderOfAddition(): void
    {
        $prioritizedMiddleware1 = $this->createMock(IMiddleware::class);
        $prioritizedMiddleware2 = $this->createMock(IMiddleware::class);
        $this->middlewareCollection->add($prioritizedMiddleware1, 1);
        $this->middlewareCollection->add($prioritizedMiddleware2, 1);
        $this->assertSame([$prioritizedMiddleware1, $prioritizedMiddleware2], $this->middlewareCollection->getAll());
    }

    public function testAddingSingleMiddlewareShowsUpInGetAll(): void
    {
        $middleware = $this->createMock(IMiddleware::class);
        $this->middlewareCollection->add($middleware);
        $this->assertSame([$middleware], $this->middlewareCollection->getAll());
    }

    public function testGetAllReturnsEmptyArrayWithNoMiddleware(): void
    {
        $this->assertEquals([], $this->middlewareCollection->getAll());
    }
}
