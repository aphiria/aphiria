<?php
namespace Opulence\Router\Dispatchers;

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
        
    }
}