<?php
use Opulence\Router\Builders\RouteBuilderRegistry;
use Opulence\Router\Middleware\MiddlewareBinding;
use Opulence\Router\Router;

// Add an ordinary route
$routeBuilderRegistry = new RouteBuilderRegistry();
$routeBuilderRegistry->map('GET', 'users/:userId=me')
    ->toMethod('UserController', 'getUser')
    ->withName('GetUser')
    ->withMiddleware('AuthMiddleware', ['roles' => 'admin']);

// Add a route with rules
$routeBuilderRegistry->map('GET', 'books/archives/:year(int,min(1987))[/:month(int,min(1),max(12)]')
    ->toMethod('BookController', 'getBooksFromArchives')
    ->withName('GetBooksFromArchives')
    ->withManyMiddleware([
        new MiddlewareBinding('AuthMiddleware', ['roles' => 'admin']),
        'SessionMiddleware'
    ]);

// Get the matched route
$router = new Router($routeBuilderRegistry->buildAll());
$matchedRoute = $router->route($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

foreach ($matchedRoute->getMiddlewareBindings() as $middlewareBinding) {
    // Resolve $middlewareBinding->getClassName()
    // Optionally inject $middlewareBinding->getProperties()
    // Use your library/framework of choice to dispatch $matchedRoute...
}
