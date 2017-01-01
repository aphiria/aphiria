<?php
use Opulence\IoC\Container;
use Opulence\Router\Builders\RouteBuilderRegistry;
use Opulence\Router\Builders\RouteGroupOptions;
use Opulence\Router\Dispatchers\ContainerDependencyResolver;
use Opulence\Router\Dispatchers\RouteActionFactory;
use Opulence\Router\Router;

// Add a group of routes that share common options
$routeActionFactory = new RouteActionFactory(new ContainerDependencyResolver(new Container));
$routeBuilderRegistry = new RouteBuilderRegistry($routeActionFactory);
$routeBuilderRegistry->group(
    new RouteGroupOptions("users/", "", false, []),
    function (RouteBuilderRegistry $routeBuilderRegistry) {
        $routeBuilderRegistry->map("GET", ":userId")
            ->toMethod("UserController", "showProfile");

        $routeBuilderRegistry->map("GET", "me")
            ->toMethod("UserController", "showMyProfile");
    });

// Actually route the request
$router = new Router($routeBuilderRegistry->buildAll());
$response = $router->route(new stdClass());