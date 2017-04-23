<?php
use Opulence\Router\Builders\RouteBuilderRegistry;
use Opulence\Router\Builders\RouteGroupOptions;
use Opulence\Router\Caching\FileRouteCache;
use Opulence\Router\Matchers\RouteMatcher;
use Opulence\Router\RouteFactory;
use Opulence\Router\RouteNotFoundException;

require __DIR__ . '/../vendor/autoload.php';

// Register our routes
$routesCallback = function (RouteBuilderRegistry $routes) {
    $routes->group(
        new RouteGroupOptions('users/', '', false, []),
        function (RouteBuilderRegistry $routes) {
            $routes->map('GET', ':userId')
                ->toMethod('UserController', 'getUser');

            $routes->map('GET', 'me')
                ->toMethod('UserController', 'showMyProfile');
        });
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
