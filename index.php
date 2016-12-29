<?php
use Opulence\IoC\Container;
use Opulence\Router\Dispatchers\ContainerDependencyResolver;
use Opulence\Router\Dispatchers\MiddlewarePipeline;
use Opulence\Router\Dispatchers\RouteDispatcherFactory;
use Opulence\Router\RouteGroupOptions;
use Opulence\Router\RouteBuilderRegistry;
use Opulence\Router\Router;

$routeDispatcherFactory = new RouteDispatcherFactory(
    new ContainerDependencyResolver(new Container),
    new MiddlewarePipeline()
);
$routeBuilderRegistry = new RouteBuilderRegistry($routeDispatcherFactory, new RouteParser());

// Add an ordinary route
$routeBuilderRegistry->map("GET", "users/:userId")
        ->toMethod("UserController", "showProfile")
        ->withName("UserProfile");

// Add a group of routes that share common options
$routeBuilderRegistry->group(
    new RouteGroupOptions("users/", "", false, []), 
    function(RouteBuilderRegistry $routeBuilderRegistry) {
        $routeBuilderRegistry->map("GET", ":userId")
           ->toMethod("UserController", "showProfile");
        
        $routeBuilderRegistry->map("GET", "me")
           ->toMethod("UserController", "showMyProfile");
});

// Actually route the request
$router = new Router($routeBuilderRegistry->buildAll());
$response = $router->route(new stdClass());