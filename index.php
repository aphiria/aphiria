<?php
use Opulence\Router\Route;
use Opulence\Router\RouteGroupOptions;
use Opulence\Router\RouteMapBuilderRegistry;
use Opulence\Router\Router;

$routeMapBuilderRegistry = new RouteMapBuilderRegistry(new RouteParser());
$routeMapBuilderRegistry->map(new Route("GET", "users/:userId"))
        ->toController("UserController", "showProfile")
        ->withName("UserProfile");

$routeMapBuilderRegistry->group(
    new RouteGroupOptions("foo/", "", false, []), 
    function(RouteMapBuilderRegistry $routeMapBuilderRegistry) {
        $routeMapBuilderRegistry->map(new Route("GET", "bar"))
           ->toController("FooController", "showFoo");
});

$router = new Router($routeMapBuilderRegistry->buildAll());