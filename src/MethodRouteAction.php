<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Routing;

/**
 * Defines a route action that uses a method
 */
class MethodRouteAction extends RouteAction
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
