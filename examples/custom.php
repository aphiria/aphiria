<?php
use Opulence\Router\Builders\RouteBuilderRegistry;
use Opulence\Router\Matchers\RouteMatcher;
use Opulence\Router\Parsers\RouteTemplateParser;
use Opulence\Router\Router;

// Add an ordinary route
$routeBuilderRegistry = new RouteBuilderRegistry(new RouteTemplateParser());
$routeBuilderRegistry->map('GET', 'users/:userId')
    ->toMethod('UserController', 'showProfile')
    ->withName('UserProfile');
$routeBuilderRegistry->map('GET', 'users/age/:{minAge|int|min(0)}-:{maxAge|int}')
    ->toMethod('UserController', 'showUsersInAgeRange')
    ->withName('UsersInAgeRange');

// Actually route the request
$router = new Router($routeBuilderRegistry->buildAll(), new RouteMatcher());
$matchedRoute = $router->route($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
