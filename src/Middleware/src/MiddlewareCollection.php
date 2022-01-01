<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Middleware;

/**
 * Defines a collection of middleware
 */
final class MiddlewareCollection
{
    /** @var array<int, array{middleware: IMiddleware, priority: int}> The list of structs that contain middleware and priorities */
    private array $middlewareWithPriorities = [];

    /**
     * Adds middleware to the collection
     *
     * @param IMiddleware $middleware The middleware to add
     * @param int|null $priority The optional priority of the middleware (lower number => higher priority)
     */
    public function add(IMiddleware $middleware, int $priority = null): void
    {
        $this->middlewareWithPriorities[] = ['middleware' => $middleware, 'priority' => $priority ?? \PHP_INT_MAX];
    }

    /**
     * Gets all the middleware in the collection
     *
     * @return list<IMiddleware> The list of middleware
     */
    public function getAll(): array
    {
        \usort($this->middlewareWithPriorities, static fn (array $a, array $b): int => $a['priority'] <=> $b['priority']);
        $prioritizedMiddleware = [];

        foreach ($this->middlewareWithPriorities as $middlewareWithPriority) {
            $prioritizedMiddleware[] = $middlewareWithPriority['middleware'];
        }

        return $prioritizedMiddleware;
    }
}
