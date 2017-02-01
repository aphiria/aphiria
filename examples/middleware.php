<?php
use Opulence\Router\Middleware\MiddlewareMetadata;
use Opulence\Router\Builders\RouteBuilderRegistry;
use Opulence\Router\Router;

// Add an ordinary route
$routeBuilderRegistry = new RouteBuilderRegistry();
$routeBuilderRegistry->map('GET', 'users/:userId=me')
    ->toMethod('UserController', 'showProfile')
    ->withName('UserProfile')
    ->withMiddleware('AuthMiddleware', ['roles' => 'admin']);
$routeBuilderRegistry->map('GET', 'users/age/:{minAge|int|min(0)}-:{maxAge|int}')
    ->toMethod('UserController', 'showUsersInAgeRange')
    ->withName('UsersInAgeRange')
    ->withManyMiddleware([
        new MiddlewareMetadata('AuthMiddleware', ['roles' => 'admin']),
        'SessionMiddleware'
    ]);

// Actually route the request
$router = new Router($routeBuilderRegistry->buildAll());
$matchedRoute = $router->route($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

foreach ($matchedRoute->getMiddlewareMetadata() as $middlewareMetadata) {
    // Resolve $middlewareMetadata->getClassName()
    // Optionally inject $middlewareMetadata->getProperties()
}
