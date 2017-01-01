<?php
namespace Opulence\Router\Matchers;

use Opulence\Router\Routes\RouteCollection;

/**
 * Defines the interface for route matchers to implement
 */
interface IRouteMatcher
{
    public function tryMatch($request, RouteCollection $routes, MatchedRoute &$matchedRoute) : bool;
}