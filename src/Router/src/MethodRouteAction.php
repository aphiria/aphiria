<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing;

/**
 * Defines a route action that uses a method
 */
final class MethodRouteAction extends RouteAction
{
    /**
     * @param string $className The name of the class the route routes to
     * @param string $methodName The name of the method the route routes to
     */
    public function __construct(string $className, string $methodName)
    {
        parent::__construct($className, $methodName, null);
    }
}
