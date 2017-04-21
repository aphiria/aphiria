<?php
use Opulence\Router\Builders\RouteBuilderRegistry;
use Opulence\Router\Builders\RouteGroupOptions;
use Opulence\Router\Matchers\RouteMatcher;
use Opulence\Router\RouteNotFoundException;

$routes = new RouteBuilderRegistry();

// Add a group of routes that share common options
$routes->group(
    new RouteGroupOptions('users/', '', false, []),
    function (RouteBuilderRegistry $routeBuilderRegistry) {
        $routeBuilderRegistry->map('GET', ':userId')
            ->toMethod('UserController', 'getUser');

        $routeBuilderRegistry->map('GET', 'me')
            ->toMethod('UserController', 'showMyProfile');
    });

// Get the matched route
try {
    $matchedRoute = (new RouteMatcher)->match(
        $_SERVER['REQUEST_METHOD'],
        $_SERVER['HTTP_HOST'],
        $_SERVER['REQUEST_URI'],
        [],
        $routes->buildAll()
    );
    
    // Use your library/framework of choice to dispatch $matchedRoute...
} catch (RouteNotFoundException $ex) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404);
    exit;
}
