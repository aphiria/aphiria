<h1>Table of Contents</h1>

1. [Introduction](#introduction)
    1. [Why This Library?](#why-this-library)
    1. [Installation](#installation)
2. [Basic Usage](#basic-usage)
    1. [URI Syntax](#uri-syntax)
    2. [Route Builders](#route-builders)
3. [Route Action](#route-actions)
4. [Binding Middleware](#binding-middleware)
    1. [Middleware Properties](#middleware-properties)
5. [Route Variable Rules](#route-variable-rules)
    1. [Making Your Own Custom Rules](#making-your-own-custom-rules)
    2. [Built-In Rules](#built-in-rules)
6. [Grouping Routes](#grouping-routes)
7. [Header Matching](#header-matching)
8. [Micro-Library](#micro-library)

<h1 id="introduction">Introduction</h1>

This library is a route matching library.  In other words, it lets you define your routes, and attempts to match an input request to ones of those routes.

<h2 id="why-this-library">Why This Library?</h2>

There are so many routing libraries out there?  Why this one?  Well, there are a few reasons:

* It is incredibly fast
* It isn't coupled to any library/framework
* It supports things that other route matching libraries do not support, like:
    * Binding framework-agnostic middleware
    * Binding both controller methods and closures to the route action
    * The ability to enforce rules on route variables
    * The ability to add your own customized route variable rules
* You can match on header values, which makes versioning your routes a cinch
* Its fluent syntax keeps you from having to memorize how to set up config arrays
* It is built to support the latest PHP 7.1 features

> **Note:** This is *not* a a route dispatching library.  This library does not call controllers or closures on the matched route.  Why?  Usually, such actions are tightly coupled to an HTTP library or to a framework.  By not dispatching the matched route, you're free to use the library/framework of your choice, while still getting the benefits of performance and fluent syntax.

<h2 id="installation">Installation</h2>
This library requires PHP 7.1 and above.

<h1 id="basic-usage">Basic Usage</h1>

Out of the box, this library provides a fluent syntax to help you build your routes with ease.  In a nutshell, you build your routes and pass them into a route matcher.  Let's look at a working example:

```php
use Opulence\Router\Builders\RouteBuilderRegistry;
use Opulence\Router\Caching\FileRouteCache;
use Opulence\Router\Matchers\RouteMatcher;
use Opulence\Router\RouteFactory;
use Opulence\Router\RouteNotFoundException;

// Register our routes
$routesCallback = function (RouteBuilderRegistry $routes) {
    $routes->map('GET', 'books/:bookId')
        ->toMethod('BookController', 'getBooksById')
        ->withName('GetBooksById');
};
$routeFactory = new RouteFactory($routesCallback, new FileRouteCache(__DIR__ . '/routes.cache'));

try {
    // Get the matched route
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

Using our example, if we hit `example.com/books/123`, then `$matchedRoute` would be an instance of `MatchedRoute`.  Grabbing the controller method is as simple as:

```php
$matchedRoute->getAction()->getControllerName(); // "BookController"
$matchedRoute->getAction()->getMethodName(); // "getBooksById"
```

To get the route variables, call:

```php
$matchedRoute->getRouteVars(); // ["bookId" => "123"]
```

Routes are defined in a callback that will be executed once and then cached.  To actually dispatch `$matchedRoute`, use the library/framework of your choice.

> **Note:** If you're using another HTTP library (eg Opulence, Symfony, or Laravel) in your application, it's better to use their methods to get the request method, host, and URI.  They account for things like trusted proxies as well as more robust handling of certain request headers.

<h2 id="uri-syntax">URI Syntax</h2>

Opulence provides a simple syntax for your URIs.  Route variables are written as `:varName`.  If you want to specify a default value, then you'd write

```php
:varName=defaultValue
```

If you'd like to use [rules](#route-variable-rules), then pass them in parentheses after the variable:
```php
:varName(rule1,rule2(param1,param2))
```

If a rule does not take any parameters, then no parentheses are required.

If part of your route is optional, then surround it with brackets.  For example, the following will match both `archives/2017` and `archives/2017/7`:
```php
archives/:year[/:month]
```

Optional route parts can be nested:

```php
archives/:year[/:month[/:day]]
```

<h2 id="route-builders">Route Builders</h2>

Each time you call `$routes->map()`, you're creating a new `RouteBuilder`.  It accepts the following parameters:

* `$httpMethods`
    * The HTTP method or list of methods to match on (eg `'GET'` or `['POST', 'GET']`)
* `$pathTemplate`
    * The path for this route ([read about syntax](#syntax))
* `$hostTemplate` (optional)
    * The optional host template for this route  ([read about syntax](#syntax))
* `$isHttpsOnly` (optional)
    * Whether or not this route is HTTPS-only
* `$headersToMatch` (optional)
    * The mapping of header names => values to match on

Route builders give you a fluent syntax for mapping your routes to closures or controller methods.  They also let you [bind any middleware](#binding-middleware) classes and properties to the route.

<h1 id="route-actions">Route Actions</h1>

Opulence supports mapping routes to both controller methods and to closures:

```php
$routesCallback = function (RouteBuilderRegistry $routes) {
    // Map to a controller method
    $routes->map('GET', 'users/:userId')
        ->toMethod('UserController', 'getUserById');

    // Map to a closure
    $routes->map('GET', 'users/:userId/name')
        ->toClosure(function () {
            // Handle the request...
        });
};
```

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

Under the hood, these class names get converted to instances of `MiddlewareBinding`.  To get the middleware from the matched route, call:

```php
$matchedRoute->getMiddlewareBindings();
```

<h2 id="middleware-properties">Middleware Properties</h2>

Some frameworks, such as Opulence and Laravel, let you bind properties to middleware.  For example, if you have an `AuthMiddleware`, but need to bind the user role that's necessary to access that route, you might want to pass in the required user role.  Here's how you can do it:

```php
$route->withMiddleware('AuthMiddleware', ['role' => 'admin']);
// Or
$route->withManyMiddleware([
    new MiddlewareBinding('AuthMiddleware', ['role' => 'admin']),
    // Other middleware...
]);
```

<h1 id="route-variable-rules">Route Variable Rules</h2>

You can enforce certain rules to pass before matching on a route.  These rules come after variables, and must be enclosed in parentheses.  For example, if you want an integer to fall between two values, you can specify a route of
```php
:month(int,min(1),max(12))
```

<h2 id="making-your-own-custom-rules">Making Your Own Custom Rules</h2>

You can register your own rule by implementing `IRule`.  Let's make a rule that enforces a certain minimum string length:

```php
use Opulence\Router\UriTemplates\Rules\IRule;

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

$routesCallback = function (RouteBuilderRegistry $routes) {
    // Register our routes...
};
$ruleFactory = new RuleFactory(
    $routesCallback,
    new FileRouteCache('/tmp/routes.cache'),
    new RouteBuilderRegistry(new UriTemplateCompiler($ruleFactory))
);
```

We can now use the slug to use this rule:  `users/names/:name(minLength(4))`.

<h2 id="built-in-rules">Built-In Rules</h2>

The following rules are built-into Opulence:

* `alpha`
* `alphanumeric`
* `between($min, $max, $isInclusive = true)`
* `date(string $commaSeparatedListOfAcceptableFormats)`
* `in(string $commaSeparatedListOfAcceptableValues)`
* `int`
* `notIn(string $commaSeparatedListOfUnacceptableValues)`
* `numeric`
* `regex(string $regex)`
* `uuidv4`

<h1 id="grouping-routes">Grouping Routes</h1>

Often times, a lot of your routes will share similar properties such as hosts/paths/headers to match on, or middleware.  You can group these routes together using `RouteBuilderRegistry::group()`:

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

PHP is irritatingly difficult to extract headers from `$_SERVER`.  If you're using a library/framework to grab headers, then use that.  Otherwise, you can use the `HeaderParser`:

```php
use Opulence\Router\Requests\HeaderParser;

$headers = (new HeaderParser)->parseHeaders($_SERVER);
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