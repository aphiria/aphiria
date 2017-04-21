<?php
use Opulence\Router\ClosureRouteAction;
use Opulence\Router\Matchers\RouteMatcher;
use Opulence\Router\Route;
use Opulence\Router\RouteNotFoundException;
use Opulence\Router\UriTemplates\UriTemplate;

// Create a route manually
// The second param in UriTemplate::construct() is the number of capturing groups in the regex
$route = new Route(
    'GET',
    new UriTemplate('foo\.com/users/(\d+)', false, ['userId']),
    new ClosureRouteAction(function ($routeVars) {
        return "Hello, {$routeVars['userId']}";
    })
);

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
