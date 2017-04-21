<?php
use Opulence\Router\Builders\RouteBuilderRegistry;
use Opulence\Router\Matchers\RouteMatcher;
use Opulence\Router\RouteNotFoundException;
use Opulence\Router\UriTemplates\Compilers\RegexUriTemplateCompiler;
use Opulence\Router\UriTemplates\Rules\RuleFactory;
use Opulence\Router\UriTemplates\Rules\RuleFactoryRegistrant;

// Setup our URI template compiler manually
$uriTemplateCompiler = new RegexUriTemplateCompiler((new RuleFactoryRegistrant)->registerRuleFactories(new RuleFactory));
$routes = new RouteBuilderRegistry($uriTemplateCompiler);

// Add an ordinary route
$routes->map('GET', 'users/:userId')
    ->toMethod('UserController', 'getUser')
    ->withName('GetUser');

// Add a route with rules
// Matches "books/archives/2013" and "books/archives/2013/2"
$routes->map('GET', 'books/archives/:year(int)[/:month(int,min(1),max(12))]')
    ->toMethod('BookController', 'getBooksFromArchives')
    ->withName('GetBooksFromArchives');

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
