<?php
use Opulence\Router\Builders\RouteBuilderRegistry;
use Opulence\Router\Router;

$routeBuilderRegistry = new RouteBuilderRegistry();

// Add an ordinary route
$routeBuilderRegistry->map('GET', 'users/:userId')
    ->toMethod('UserController', 'getUser')
    ->withName('GetUser');

// Add a route with rules
// Matches "books/archives/2013" and "books/archives/2013/2"
$routeBuilderRegistry->map('GET', 'books/archives/:year(int)[/:month(int,min(1),max(12))]')
    ->toMethod('BookController', 'getBooksFromArchives')
    ->withName('GetBooksFromArchives');

// Get the matched route
$router = new Router($routeBuilderRegistry->buildAll());
$matchedRoute = $router->route($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

// Use your library/framework of choice to dispatch $matchedRoute...

// You can also generate URIs within your views using the route names
// This would print "books/archives/2013/2"
$routeCollection->getNamedRoute('GetBooksFromArchives')
    ->getUriTemplate()
    ->buildTemplate(['year' => 2013, 'month' => 2]);
