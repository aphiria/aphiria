<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing;

use Closure;

/**
 * Defines a route action that uses a closure
 */
class ClosureRouteAction extends RouteAction
{
    /**
     * @param Closure $closure The closure the route routes to
     */
    public function __construct(Closure $closure)
    {
        parent::__construct(null, null, $closure);
    }

    /**
     * @inheritdoc
     */
    public function usesMethod() : bool
    {
        return false;
    }
}
