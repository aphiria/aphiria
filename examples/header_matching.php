<?php
use Opulence\Router\Builders\RouteBuilderRegistry;
use Opulence\Router\Router;

$routes = new RouteBuilderRegistry();

// Add a route with a header to match on
$routes->map('GET', 'comments', null, false, ['API VERSION' => 'v1.0'])
    ->toMethod('CommentController', 'getAllComments');

// Since PHP doesn't have a native way of grabbing all request headers, we'll build them ourselves
// Feel free to use a library of your choice to do this for you, if you'd like
$headers = [];
// These headers do not have the HTTP_ prefix
$specialCaseHeaders = [
    'AUTH_TYPE' => true,
    'CONTENT_LENGTH' => true,
    'CONTENT_TYPE' => true,
    'PHP_AUTH_DIGEST' => true,
    'PHP_AUTH_PW' => true,
    'PHP_AUTH_TYPE' => true,
    'PHP_AUTH_USER' => true
];

foreach ($_SERVER as $key => $value) {
    $uppercasedKey = strtoupper($key);

    if (isset($specialCaseHeaders[$uppercasedKey]) || strpos($uppercasedKey, 'HTTP_') === 0) {
        $headers[$uppercasedKey] = (array)$value;
    }
}

// Get the matched route
$router = new Router($routes->buildAll());
$matchedRoute = $router->route($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], $headers);

// Use your library/framework of choice to dispatch $matchedRoute...
