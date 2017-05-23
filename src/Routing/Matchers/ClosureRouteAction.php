<?php
namespace Opulence\Routing\Matchers;

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
