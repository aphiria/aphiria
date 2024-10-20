<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Middleware;

use IteratorAggregate;

/**
 * Defines a collection of middleware
 */
final class MiddlewareCollection
{
    /**
     * The list of middleware values in sorted priority order
     * @note Each invocation of this re-orders the list of middleware, and should only be used after all middleware have been added
     *
     * @var list<IMiddleware>
     */
    public array $values {
        get {
            \usort($this->middlewareWithPriorities, static fn (array $a, array $b): int => $a['priority'] <=> $b['priority']);
            $prioritizedMiddleware = [];

            foreach ($this->middlewareWithPriorities as $middlewareWithPriority) {
                $prioritizedMiddleware[] = $middlewareWithPriority['middleware'];
            }

            return $prioritizedMiddleware;
        }
    }
    /** @var list<array{middleware: IMiddleware, priority: int}> The list of structs that contain middleware and priorities */
    private array $middlewareWithPriorities = [];

    /**
     * Adds middleware to the collection
     *
     * @param IMiddleware $middleware The middleware to add
     * @param int|null $priority The optional priority of the middleware (lower number => higher priority)
     */
    public function add(IMiddleware $middleware, ?int $priority = null): void
    {
        $this->middlewareWithPriorities[] = ['middleware' => $middleware, 'priority' => $priority ?? \PHP_INT_MAX];
    }
}
