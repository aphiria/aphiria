<?php
use Opulence\IoC\Container;
use Opulence\Router\Dispatchers\ContainerDependencyResolver;
use Opulence\Router\Dispatchers\MiddlewarePipeline;
use Opulence\Router\Dispatchers\RouteDispatcherFactory;
use Opulence\Router\Route;
use Opulence\Router\RouteGroupOptions;
use Opulence\Router\RouteMapBuilderRegistry;
use Opulence\Router\Router;

$routeDispatcherFactory = new RouteDispatcherFactory(
    new ContainerDependencyResolver(new Container),
    new MiddlewarePipeline()
);
$routeMapBuilderRegistry = new RouteMapBuilderRegistry($routeDispatcherFactory, new RouteParser());

// Add an ordinary route
$routeMapBuilderRegistry->map(new Route("GET", "users/:userId"))
        ->toController("UserController", "showProfile")
        ->withName("UserProfile");

// Add a group of routes that share common options
$routeMapBuilderRegistry->group(
    new RouteGroupOptions("users/", "", false, []), 
    function(RouteMapBuilderRegistry $routeMapBuilderRegistry) {
        $routeMapBuilderRegistry->map(new Route("GET", ":userId"))
           ->toController("UserController", "showProfile");
        
        $routeMapBuilderRegistry->map(new Route("GET", "me"))
           ->toController("UserController", "showMyProfile");
});

// Actually route the request
$router = new Router($routeMapBuilderRegistry->buildAll());
$response = $router->route(new stdClass());