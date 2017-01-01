<?php
namespace Opulence\Router\Dispatchers;

use Opulence\Router\Matchers\MatchedRoute;

/**
 * Defines the interface for route dispatchers to implement
 */
interface IRouteDispatcher
{
    public function dispatch($request, MatchedRoute $matchedRoute);
}