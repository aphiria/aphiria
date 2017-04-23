<?php
use Opulence\Router\ClosureRouteAction;
use Opulence\Router\Matchers\RouteMatcher;
use Opulence\Router\Route;
use Opulence\Router\RouteCollection;
use Opulence\Router\RouteNotFoundException;
use Opulence\Router\UriTemplates\UriTemplate;

require __DIR__ . '/../vendor/autoload.php';

// Create a route manually
// The second param in UriTemplate::construct() is whether or not the URI is absolute
$routes = new RouteCollection();
$routes->add(new Route(
    'GET',
    new UriTemplate('^[^/]+/users/(\d+)$', false, ['userId']),
    new ClosureRouteAction(function () {
        return "Hello, world";
    })
));

// Get the matched route
try {
    $matchedRoute = (new RouteMatcher($routes))->match(
        $_SERVER['REQUEST_METHOD'],
        $_SERVER['HTTP_HOST'],
        $_SERVER['REQUEST_URI']
    );

    // Use your library/framework of choice to dispatch $matchedRoute...
} catch (RouteNotFoundException $ex) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404);
    exit;
}
