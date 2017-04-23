<?php
use Opulence\Router\Builders\RouteBuilderRegistry;
use Opulence\Router\Caching\FileRouteCache;
use Opulence\Router\Matchers\RouteMatcher;
use Opulence\Router\Middleware\MiddlewareBinding;
use Opulence\Router\RouteFactory;
use Opulence\Router\RouteNotFoundException;

// Register our routes
$routesCallback = function (RouteBuilderRegistry $routes) {
    // Add a route with a single middleware class
    $routes->map('GET', 'users/:userId')
        ->toMethod('UserController', 'getUser')
        ->withName('GetUser')
        ->withMiddleware('AuthMiddleware', ['roles' => 'admin']);

    // Add a route with many middleware classes
    $routes->map('GET', 'books/archives/:year(int)[/:month(int,between(1,12))]')
        ->toMethod('BookController', 'getBooksFromArchives')
        ->withName('GetBooksFromArchives')
        // This supports both MiddlewareBinding objects and the names of session middleware classes
        ->withManyMiddleware([
            new MiddlewareBinding('AuthMiddleware', ['roles' => 'admin']),
            'SessionMiddleware'
        ]);
};
$routeFactory = new RouteFactory($routesCallback, new FileRouteCache(__DIR__ . '/routes.cache'));

// Get the matched route
try {
    $matchedRoute = (new RouteMatcher($routeFactory->createRoutes()))->match(
        $_SERVER['REQUEST_METHOD'],
        $_SERVER['HTTP_HOST'],
        $_SERVER['REQUEST_URI']
    );

    // Use your library/framework of choice to dispatch $matchedRoute...
} catch (RouteNotFoundException $ex) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404);
    exit;
}
