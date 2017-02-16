<?php
use Opulence\Router\Builders\RouteBuilderRegistry;
use Opulence\Router\Matchers\RouteMatcher;
use Opulence\Router\Router;
use Opulence\Router\UriTemplates\Parsers\RegexUriTemplateParser;

// Add an ordinary route
$routeBuilderRegistry = new RouteBuilderRegistry(new RegexUriTemplateParser());
$routeBuilderRegistry->map('GET', 'users/:userId')
    ->toMethod('UserController', 'showProfile')
    ->withName('UserProfile');

// Add a route with rules
$routeBuilderRegistry->map('GET', 'users/age/:minAge(int,min(0))-:maxAge(int)')
    ->toMethod('UserController', 'showUsersInAgeRange')
    ->withName('UsersInAgeRange');

// Get the matched route
$router = new Router($routeBuilderRegistry->buildAll(), new RouteMatcher());
$matchedRoute = $router->route($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

// Use your library/framework of choice to dispatch $matchedRoute...
