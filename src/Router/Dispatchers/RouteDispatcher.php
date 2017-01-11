<?php
namespace Opulence\Router\Dispatchers;

use Opulence\Router\Matchers\MatchedRoute;

/**
 * Defines the route dispatcher
 */
class RouteDispatcher implements IRouteDispatcher
{
    /** @var IMiddlewarePipeline The middleware pipeline */
    private $middlewarePipeline = null;

    public function __construct(IMiddlewarePipeline $middlewarePipeline)
    {
        $this->middlewarePipeline = $middlewarePipeline;
    }

    public function dispatch($request, MatchedRoute $matchedRoute)
    {
        // Todo: Need to set the matched route's class instance, if that's what it was using as a controller
    }
}