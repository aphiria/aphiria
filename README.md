<h1>Table of Contents</h1>

1. [Introduction](#introduction)
    1. [Why This Library?](#why-this-library)
2. [Basic Usage](#basic-usage)
    1. [Route Builders](#route-builders)
    2. [Syntax](#syntax)
    3. [Matched Routes](#matched-routes)
3. [Binding Middleware](#binding-middleware)
    1. [Middleware Properties](#middleware-properties)
4. [Route Variable Rules](#route-variable-rules)
    1. [Built-In Rules](#built-in-rules)
5. [Grouping Routes](#grouping-routes)
6. [Header Matching](#header-matching)
7. [Micro-Library](#micro-library)

<h1 id="introduction">Introduction</h1>

This library is a route matching library.  In other words, it lets you define your routes, and returns the matched route given the request host and path.

<h2 id="why-this-library">Why This Library?</h2>
There are so many routing libraries out there?  Why this one?  Well, there are a few reasons:

* It is incredibly fast
* It isn't coupled to any other library/framework
* It supports things that other route matching libraries do not support, like:
    * Binding framework-agnostic middleware
    * Binding both controller methods and closures to the route action
    * The ability to enforce rules on route variables, and with the ability to add your own customized route variable rules
* You can match on header values, which makes versioning your routes a cinch
* Its fluent syntax keeps you from having to memorize how to set up config arrays
* It is built to support the latest PHP 7.1 features

> **Note:** This is *not* a a route dispatching library.  This library does not call controllers or closures on the matched route.  Why?  Usually, such actions are tightly coupled to an HTTP library or to a framework.  By not dispatching the matched route, you're free to use the library/framework of your choice, while still getting the benefits of performance and fluent syntax.

<h1 id="basic-usage">Basic Usage</h1>
Out of the box, this library provides a fluent syntax to help you build your routes with ease.  In a nutshell, you build your routes and pass them into a route matcher.  Let's look at a simple and complete example:

```php
use Opulence\Router\Builders\RouteBuilderRegistry;
use Opulence\Router\Caching\FileRouteCache;
use Opulence\Router\Matchers\RouteMatcher;
use Opulence\Router\RouteFactory;
use Opulence\Router\RouteNotFoundException;

// Register our routes
$routesCallback = function (RouteBuilderRegistry $routes) {
    // Add an ordinary route
    $routes->map('GET', 'books/:bookId')
        ->toMethod('BookController', 'getBooksById')
        ->withName('GetBooksById');

    // Add a route with rules
    // Matches "books/archives/2013" and "books/archives/2013/2"
    $routes->map('GET', 'books/archives/:year(int)[/:month(int,between(1,12))]')
        ->toMethod('BookController', 'getBooksFromArchives')
        ->withName('GetBooksFromArchives');
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
```

We define our routes inside a callback that will be executed once and then cached.  To actually dispatch `$matchedRoute`, use the library/framework of your choice.

<h2 id="route-builders">Route Builders</h2>
Each time you call `$routes->map()`, you're creating a new `RouteBuilder`.  These give you a fluent syntax for mapping your routes to closures or controller methods.  They also let you [bind any middleware](#binding-middleware) classes and properties to the route.

<h2 id="syntax">Syntax</h2>
Opulence provides a simple syntax for your URIs.  Route variables are written as `:varName`.  If you want to specify a default value, then you'd write `:varName=defaultValue`.  If you'd like to use rules, then pass them in parentheses after the variable:   `:varName(rule1,rule2(param1,param2))`.  If a rule does not take any parameters, then no parentheses are required.

If part of your route is optional, then surround it with brackets.  For example, the following will match both `archives/2017` and `archives/2017/7`: `archives/:year[/:month]`.

<h2 id="matched-routes">Matched Routes</h2>
The route matcher returns a matched route on success.  It will contain three simple methods:

* `getAction()`
    * The action (either `Closure` or class name/method this route maps to)
* `getMiddlewareBindings()`
    * The list of middleware class names/properties this route uses
* `getRouteVars()`
    * The mapping of route variable names to values for this route
    * For example, if the route is `users/:userId` and the request URI is `/users/123`, then `getRouteVars()` would return `['userId' => '123']`.

<h1 id="binding-middleware">Binding Middleware</h1>
Middleware are a great way to modify both the request and the response on an endpoint.  Opulence lets you define middleware on your endpoints without binding you to any particular library/framework's middleware implementations.

To bind a single middleware class to your route, call:

```php
$route->withMiddleware('FooMiddleware');
```

To bind many middleware classes, call:

```php
$route->withManyMiddleware([
    'FooMiddleware',
    'BarMiddleware'
]);
```

<h2 id="middleware-properties">Middleware Properties</h2>
Some frameworks such as Opulence and Laravel let you bind properties to middleware.  For example, if you have an `AuthMiddleware`, but need to bind the route that's necessary to access the route, you might want to pass in the requisite user role.  Here's how you can do it:

```php
$route->withMiddleware('AuthMiddleware', ['role' => 'admin']);
// Or
$route->withManyMiddleware([
    new MiddlewareBinding('AuthMiddleware', ['role' => 'admin']),
    // Other middleware
]);
```

<h1 id="route-variable-rules">Route Variable Rules</h2>
You can enforce certain rules to pass before matching on a route.  These rules come after variables, and must be enclosed in parentheses.  For example, if you want an integer to fall between two values, you can specify a route of `:month(int,min(1),max(12))`.

You can register your own rule by implementing `Opulence\Router\UriTemplates\Rules\IRule`.  Let's make a rule that enforces a certain minimum string length:

```php
class MinLengthRule implements IRule
{
    /** @var int The required minimum length */
    private $minLength = 0;

    /**
     * @param int The required minimum length
     */
    public function __construct(int $minLength)
    {
        $this->minLength = $minLength;
    }

    /**
     * @inheritdoc
     */
    public static function getSlug() : string
    {
        return 'minLength';
    }

    /**
     * @inheritdoc
     */
    public function passes($value) : bool
    {
        return mb_strlen($value) >= $this->minLength;
    }
}
```

Let's register our rule with the rule factory:

```php
use Opulence\Router\UriTemplates\Rules\RuleFactory;
use Opulence\Router\UriTemplates\Rules\RuleFactoryRegistrant;

// Register some built-in rules to our factory
$ruleFactory = (new RuleFactoryRegistrant)->registerRuleFactories(new RuleFactory);

// Register our custom rule
$ruleFactory->registerRuleFactory(MinLengthRule::getSlug(), function (int $minLength) {
    return new MinLengthRule($minLength);
});
```

Finally, register this rule factory with the URI template compiler:

```php
use Opulence\Router\Builders\RouteBuilderRegistry;
use Opulence\Router\Caching\FileRouteCache;
use Opulence\Router\RuleFactory;
use Opulence\Router\UriTemplates\Compilers\UriTemplateCompiler;

$uriTemplateCompiler = new UriTemplateCompiler($ruleFactory);
$routeBuilderRegistry = new RouteBuilderRegistry($uriTemplateCompiler);
$ruleCallback = function (RouteBuilderRegistry $routes) {
    // Register our rules
};
$ruleFactory = new RuleFactory(
    $ruleCallback,
    new FileRouteCache('/tmp/routes.cache'),
    $routeBuilderRegistry
);
```

We can now use the slug to use this rule:  `users/names/:name(minLength(4))`.

<h2 id="built-in-rules">Build-In Rules</h2>
The following rules are built-into Opulence:

* `alpha`
* `alphanumeric`
* `between($min, $max, $isInclusive = true)`
* `date($listOfAcceptableFormats)`
* `in($listOfAcceptableValues)`
* `int`
* `notIn($listOfUnacceptableValues)`
* `numeric`
* `regex($regex)`
* `uuidv4`

<h1 id="grouping-routes">Grouping Routes</h1>
Often times, a lot of your routes will share similar properties such as hosts/paths/headers to match on and middleware.  You can group these routes together using `RouteBuilderRegistry::group()`:

```php
$routesCallback = function (RouteBuilderRegistry $routes) {
    $routes->group(
        new RouteGroupOptions('users/', 'example.com', false, ['FooMiddleware'], ['API-VERSION' => 'v1.0']),
        function (RouteBuilderRegistry $routes) {
            $routes->map('GET', ':userId')
                ->toMethod('UserController', 'getUser');

            $routes->map('GET', 'me')
                ->toMethod('UserController', 'showMyProfile');
        });
};
```

This creates two routes (`example.com/users/:userId` and `example.com/users/me`) with `FooMiddleware` and an API version of `v1.0`  bound to both.

<h1 id="header-matching">Header Matching</h1>
Sometimes, you might find it helpful to be able to specify certain header values to match on.  Opulence makes this easy:

```php
$routesCallback = function (RouteBuilderRegistry $routes) {
    // This route will require an API-VERSION value of 'v1.0'
    $routes->map('GET', 'comments', null, false, ['API-VERSION' => 'v1.0'])
        ->toMethod('CommentController', 'getAllComments');
};
```

Then, pass the header values to `RouteMatcher::match()` as a 4th parameter.

<h2 id="getting-php-headers">Getting Headers in PHP</h2>
PHP is irritatingly difficult to extract headers from `$_SERVER`.  If you're using a library/framework to grab headers, then use that.  Otherwise, you can use the following:

```php
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
$headers = [];

foreach ($_SERVER as $key => $value) {
    $uppercasedKey = strtoupper($key);

    if (isset($specialCaseHeaders[$uppercasedKey]) || strpos($uppercasedKey, 'HTTP_') === 0) {
        $headers[$uppercasedKey] = (array)$value;
    }
}

// $headers now holds all the request headers
```

<h1 id="micro-library">Micro-Library</h1>
If you don't want to use the route builders, and instead would rather create the routes yourself, you can.  Here's a complete example of how:

```php
use Opulence\Router\ClosureRouteAction;
use Opulence\Router\Matchers\RouteMatcher;
use Opulence\Router\Route;
use Opulence\Router\RouteCollection;
use Opulence\Router\RouteNotFoundException;
use Opulence\Router\UriTemplates\UriTemplate;

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
```