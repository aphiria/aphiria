<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Middleware;

/**
 * Defines a middleware binding
 */
final class MiddlewareBinding
{
    /**
     * @param class-string $className The name of the middleware class
     * @param array $parameters The name => value mapping of parameters bound to the middleware
     */
    public function __construct(public string $className, public array $parameters = [])
    {
    }
}
