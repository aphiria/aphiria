<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing;

/**
 * Defines a route action
 */
readonly class RouteAction
{
    /**
     * @param class-string $className The name of the class the route routes to
     * @param string $methodName The name of the method the route routes to
     */
    public function __construct(public string $className, public string $methodName)
    {
    }
}
