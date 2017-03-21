<?php
use Opulence\Router\Builders\RouteBuilderRegistry;
use Opulence\Router\Matchers\RouteMatcher;
use Opulence\Router\Router;
use Opulence\Router\UriTemplates\Compilers\RegexUriTemplateCompiler;
use Opulence\Router\UriTemplates\Rules\RuleFactory;
use Opulence\Router\UriTemplates\Rules\RuleFactoryRegistrant;

// Setup our URI template compiler manually
$uriTemplateCompiler = new RegexUriTemplateCompiler((new RuleFactoryRegistrant)->registerRuleFactories(new RuleFactory));
$routeBuilderRegistry = new RouteBuilderRegistry($uriTemplateCompiler);

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
$router = new Router($routeBuilderRegistry->buildAll(), new RouteMatcher());
$matchedRoute = $router->route($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

// Use your library/framework of choice to dispatch $matchedRoute...
