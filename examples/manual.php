<?php
use Opulence\Router\ClosureRouteAction;
use Opulence\Router\Route;
use Opulence\Router\Router;
use Opulence\Router\UriTemplates\UriTemplate;

// Create a route manually
// The second param in UriTemplate::construct() is the number of capturing groups in the regex
$route = new Route(
    ['GET'],
    new UriTemplate('foo\.com/users/(?P<userId>\d+)', 1, false),
    new ClosureRouteAction(function ($routeVars) {
        return "Hello, {$routeVars['userId']}";
    }),
    ['MyMiddlewareClass'],
    'MyProfile'
);

// Get the matched route
$router = new Router([$route]);
$matchedRoute = $router->route($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

// Use your library/framework of choice to dispatch $matchedRoute...
