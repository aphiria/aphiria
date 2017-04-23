<?php
use Opulence\Router\Builders\RouteBuilderRegistry;
use Opulence\Router\Caching\FileRouteCache;
use Opulence\Router\Matchers\RouteMatcher;
use Opulence\Router\RouteFactory;
use Opulence\Router\RouteNotFoundException;
use Opulence\Router\UriTemplates\Compilers\UriTemplateCompiler;
use Opulence\Router\UriTemplates\Rules\RuleFactory;
use Opulence\Router\UriTemplates\Rules\RuleFactoryRegistrant;

require __DIR__ . '/../vendor/autoload.php';

// Register our routes
$routesCallback = function (RouteBuilderRegistry $routes) {
    // Add an ordinary route
    $routes->map('GET', 'users/:userId')
        ->toMethod('UserController', 'getUser')
        ->withName('GetUser');

    // Add a route with rules
    // Matches "books/archives/2013" and "books/archives/2013/2"
    $routes->map('GET', 'books/archives/:year(int)[/:month(int,between(1,12))]')
        ->toMethod('BookController', 'getBooksFromArchives')
        ->withName('GetBooksFromArchives');
};

// Setup our URI template compiler manually
$uriTemplateCompiler = new UriTemplateCompiler((new RuleFactoryRegistrant)->registerRuleFactories(new RuleFactory));
$routeFactory = new RouteFactory(
    $routesCallback,
    new FileRouteCache(__DIR__ . '/routes.cache'),
    new RouteBuilderRegistry($uriTemplateCompiler)
);

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
