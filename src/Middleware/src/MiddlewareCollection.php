<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Middleware;

/**
 * Defines a collection of middleware
 */
final class MiddlewareCollection
{
    /** @var IMiddleware[] The list of middleware */
    private array $middleware = [];

    /**
     * Adds middleware to the collection
     *
     * @param IMiddleware $middleware The middleware to add
     */
    public function add(IMiddleware $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    /**
     * Gets all the middleware in the collection
     *
     * @return IMiddleware[] The list of middleware
     */
    public function getAll(): array
    {
        return $this->middleware;
    }
}
