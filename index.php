<?php
use Opulence\IoC\Container;
use Opulence\Router\Builders\RouteBuilderRegistry;
use Opulence\Router\Builders\RouteGroupOptions;
use Opulence\Router\Dispatchers\ContainerDependencyResolver;
use Opulence\Router\Dispatchers\MiddlewarePipeline;
use Opulence\Router\Dispatchers\RouteActionFactory;
use Opulence\Router\Matchers\RouteMatcher;
use Opulence\Router\RegexRouteTemplate;
use Opulence\Router\Route;
use Opulence\Router\Router;

$routeActionFactory = new RouteActionFactory(new ContainerDependencyResolver(new Container));
$routeBuilderRegistry = new RouteBuilderRegistry($routeActionFactory, new RouteParser());

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

// Create a Route directly
$route = new Route(
    ["GET"],
    function ($request, $routeVars) {},
    new RegexRouteTemplate("users\/me"),
    true,
    ["MiddlewareClass"],
    new RegexRouteTemplate("example\.com"),
    "MyProfile"
);

// Actually route the request
$router = new Router(
    $routeBuilderRegistry->buildAll(),
    new RouteMatcher(),
    new RouteDispatcher(new MiddlewarePipeline())
);
$response = $router->route(new stdClass());