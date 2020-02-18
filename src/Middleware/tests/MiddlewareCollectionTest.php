<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Middleware\Tests;

use Aphiria\Middleware\IMiddleware;
use Aphiria\Middleware\MiddlewareCollection;
use PHPUnit\Framework\TestCase;

/**
 * Tests the middleware collection
 */
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
