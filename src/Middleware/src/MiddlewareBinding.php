<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Middleware;

/**
 * Defines a middleware binding, which is a wrapper around the name of the middleware + any parameters
 */
final class MiddlewareBinding
{
    /**
     * @param class-string $className The name of the middleware class
     * @param array<string, mixed> $parameters The name => value mapping of parameters bound to the middleware
     */
    public function __construct(public string $className, public array $parameters = [])
    {
    }
}
