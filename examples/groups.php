<?php
use Opulence\Router\Builders\RouteBuilderRegistry;
use Opulence\Router\Builders\RouteGroupOptions;
use Opulence\Router\Router;

// Add a group of routes that share common options
$routeBuilderRegistry = new RouteBuilderRegistry();
$routeBuilderRegistry->group(
    new RouteGroupOptions('users/', '', false, []),
    function (RouteBuilderRegistry $routeBuilderRegistry) {
        $routeBuilderRegistry->map('GET', ':userId')
            ->toMethod('UserController', 'showProfile');

        $routeBuilderRegistry->map('GET', 'me')
            ->toMethod('UserController', 'showMyProfile');
    });

// Get the matched route
$router = new Router($routeBuilderRegistry->buildAll());
$matchedRoute = $router->route($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

// Use your library/framework of choice to dispatch $matchedRoute...
