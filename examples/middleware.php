<?php
use Opulence\Router\Builders\RouteBuilderRegistry;
use Opulence\Router\Matchers\RouteMatcher;
use Opulence\Router\Middleware\MiddlewareBinding;
use Opulence\Router\RouteNotFoundException;

$routes = new RouteBuilderRegistry();

// Add an ordinary route
$routes->map('GET', 'users/:userId=me')
    ->toMethod('UserController', 'getUser')
    ->withName('GetUser')
    ->withMiddleware('AuthMiddleware', ['roles' => 'admin']);

// Add a route with rules
// Matches "books/archives/2013" and "books/archives/2013/2"
$routes->map('GET', 'books/archives/:year(int)[/:month(int,min(1),max(12))]')
    ->toMethod('BookController', 'getBooksFromArchives')
    ->withName('GetBooksFromArchives')
    ->withManyMiddleware([
        new MiddlewareBinding('AuthMiddleware', ['roles' => 'admin']),
        'SessionMiddleware'
    ]);

// Get the matched route
try {
    $matchedRoute = (new RouteMatcher)->match(
        $_SERVER['REQUEST_METHOD'],
        $_SERVER['HTTP_HOST'],
        $_SERVER['REQUEST_URI'],
        [],
        $routes->buildAll()
    );

    // Use your library/framework of choice to dispatch $matchedRoute...
} catch (RouteNotFoundException $ex) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404);
    exit;
}
