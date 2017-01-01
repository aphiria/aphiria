<?php
use Opulence\IoC\Container;
use Opulence\Router\Builders\RouteBuilderRegistry;
use Opulence\Router\Dispatchers\ContainerDependencyResolver;
use Opulence\Router\Dispatchers\MiddlewarePipeline;
use Opulence\Router\Dispatchers\RouteActionFactory;
use Opulence\Router\Dispatchers\RouteDispatcher;
use Opulence\Router\Matchers\RouteMatcher;
use Opulence\Router\Router;
use Opulence\Router\Parsers\RouteTemplateParser;

// Add an ordinary route
$routeActionFactory = new RouteActionFactory(new ContainerDependencyResolver(new Container));
$routeBuilderRegistry = new RouteBuilderRegistry($routeActionFactory, new RouteTemplateParser());
$routeBuilderRegistry->map("GET", "users/:userId")
    ->toMethod("UserController", "showProfile")
    ->withName("UserProfile");

// Actually route the request
$router = new Router(
    $routeBuilderRegistry->buildAll(),
    new RouteMatcher(),
    new RouteDispatcher(new MiddlewarePipeline())
);
$response = $router->route(new stdClass());