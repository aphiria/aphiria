<?php
use Opulence\Router\RegexRouteTemplate;
use Opulence\Router\Route;
use Opulence\Router\Router;

// Create a route manually
$route = new Route(
    "GET",
    function ($request, $routeVars) {
        return "Hello, {$routeVars["userId"]}";
    },
    new RegexRouteTemplate('users\/(?P<userId>\d+)'),
    false
);

// Actually route the request
$router = new Router([$route]);
$response = $router->route(new stdClass());