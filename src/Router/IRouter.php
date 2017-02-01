<?php
namespace Opulence\Router;

/**
 * Defines the interface for routers to implement
 */
interface IRouter
{
    public function route(string $httpMethod, string $uri) : MatchedRoute;
}
