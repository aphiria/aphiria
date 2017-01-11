<?php
use Opulence\IoC\Container;
use Opulence\Router\Builders\RouteBuilderRegistry;
use Opulence\Router\Dispatchers\ContainerDependencyResolver;
use Opulence\Router\Dispatchers\RouteActionFactory;
use Opulence\Router\Router;

// Add an ordinary route
$routeActionFactory = new RouteActionFactory(new ContainerDependencyResolver(new Container));
$routeBuilderRegistry = new RouteBuilderRegistry($routeActionFactory);
$routeBuilderRegistry->map("GET", "users/:userId")
    ->toMethod("UserController", "showProfile")
    ->withName("UserProfile");
$routeBuilderRegistry->map("GET", "users/age/:{minAge:int:min(0)}-:{maxAge:int}")
    ->toMethod("UserController", "showUsersInAgeRange")
    ->withName("UsersInAgeRange");

// Actually route the request
$router = new Router($routeBuilderRegistry->buildAll());
$response = $router->route(new stdClass());