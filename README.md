<h1>Table of Contents</h1>

1. [Introduction](#introduction)
    1. [Why Use This Library?](#why-use-this-library)
    1. [Installation](#installation)
2. [Basic Usage](#basic-usage)
    1. [Route Variables](#route-variables)
    2. [Optional Route Parts](#optional-route-parts)
    3. [Route Builders](#route-builders)
3. [Route Action](#route-actions)
4. [Binding Middleware](#binding-middleware)
    1. [Middleware Properties](#middleware-properties)
5. [Grouping Routes](#grouping-routes)
6. [Custom Constraints](#custom-constraints)
    1. [Getting Headers in PHP](#getting-php-headers)
7. [Route Variable Rules](#route-variable-rules)
    1. [Built-In Rules](#built-in-rules)
    2. [Making Your Own Custom Rules](#making-your-own-custom-rules)
8. [Caching](#caching)
9. [Micro-Library](#micro-library)

<h1 id="introduction">Introduction</h1>

This library is a route matching library.  In other words, it lets you map URIs to actions, and attempts to match an input request to ones of those routes.

<h2 id="why-use-this-library">Why Use This Library?</h2>

There are so many routing libraries out there.  Why use this one?  Well, there are a few reasons:

* It isn't coupled to _any_ library/framework
* It is fast
* It supports things that other route matching libraries do not support, like:
    * Binding framework-agnostic middleware to routes
    * Binding controller methods and closures to the route action
    * The ability to enforce rules on route variables
    * The ability to add your own customized route variable rules
* You can match on header values, which makes versioning your routes a cinch
* Its fluent syntax keeps you from having to memorize how to set up config arrays
* It is built to support the latest PHP 7.1 features

> **Note:** This is *not* a route dispatching library.  This library does not call controllers or closures on the matched route.  Why?  Usually, such actions are tightly coupled to an HTTP library or to a framework.  By not dispatching the matched route, you're free to use the library/framework of your choice, while still getting the benefits of performance and fluent syntax.

<h2 id="installation">Installation</h2>
This library requires PHP 7.1 and above.  It can be installed via <a href="https://getcomposer.org/" target="_blank">Composer</a> by including `"opulence/route-matching": "1.0.*@dev"` in your _composer.json_.

<h1 id="basic-usage">Basic Usage</h1>

Out of the box, this library provides a fluent syntax to help you build your routes with ease.  Let's look at a working example:

```php
use Opulence\Routing\Matchers\Builders\RouteBuilderRegistry;
use Opulence\Routing\Matchers\Caching\FileRouteCache;
use Opulence\Routing\Matchers\RouteFactory;
use Opulence\Routing\Matchers\RouteMatcher;
use Opulence\Routing\Matchers\RouteNotFoundException;

// Define your routes
$routesCallback = function (RouteBuilderRegistry $routes) {
    $routes->map('GET', 'books/:bookId')
        ->toMethod('BookController', 'getBooksById')
        ->withName('GetBooksById');
};

try {
    // Find a matching route
    $routeFactory = new RouteFactory(
        $routesCallback,
        new FileRouteCache('/tmp/routes.cache')
    );
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

Using our example, if we hit `example.com/books/123`, then `$matchedRoute` would be an instance of `MatchedRoute`.  Grabbing the matched controller info is as simple as:

```php
$matchedRoute->getAction()->getControllerName(); // "BookController"
$matchedRoute->getAction()->getMethodName(); // "getBooksById"
```

To get the route variables, call:

```php
$matchedRoute->getRouteVars(); // ["bookId" => "123"]
```

> **Note:** If you're using another HTTP library (eg Opulence, Symfony, or Laravel) in your application, it's better to use their methods to get the request method, host, and URI.  They account for things like trusted proxies as well as provide more robust handling of certain request headers.

<h2 id="route-variables">Route Variables</h2>

Opulence provides a simple syntax for your URIs.  To capture variables in your route, use `:varName`, eg:

```php
users/:userId/profile
```

If you want to specify a default value, then you'd write

```php
users/:userId=me/profile
```

If you'd like to use [rules](#route-variable-rules), then put them in parentheses after the variable:
```php
:varName(rule1,rule2(param1,param2))
```

<h2 id="optional-route-parts">Optional Route Parts</h2>

If part of your route is optional, then surround it with brackets.  For example, the following will match both `archives/2017` and `archives/2017/7`:
```php
archives/:year[/:month]
```

Optional route parts can be nested:

```php
archives/:year[/:month[/:day]]
```

<h2 id="route-builders">Route Builders</h2>

To build your routes, call `$routes->map()`, which accepts the following parameters:

* `string|array $httpMethods`
    * The HTTP method or list of methods to match on (eg `'GET'` or `['POST', 'GET']`)
* `string $pathTemplate`
    * The path for this route ([read about syntax](#route-variables))
* `string|null $hostTemplate` (optional)
    * The optional host template for this route  ([read about syntax](#route-variables))
* `bool $isHttpsOnly` (optional)
    * Whether or not this route is HTTPS-only

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

Here's how you can grab the middleware on a matched route:

```php
foreach ($matchedRoute->getMiddlewareBindings() as $middlewareBinding) {
    $middlewareBinding->getClassName(); // "AuthMiddleware"
    $middlewareBinding->getProperties(); // ["role" => "admin"]
}
```

<h1 id="grouping-routes">Grouping Routes</h1>

Often times, a lot of your routes will share similar properties such as hosts and paths to match on, or middleware.  You can group these routes together using `RouteBuilderRegistry::group()` and specifying the options to apply to all routes within the group:

```php
use Opulence\Routing\Matchers\Builders\RouteGroupOptions;

$routesCallback = function (RouteBuilderRegistry $routes) {
    $routes->group(
        new RouteGroupOptions('courses/:courseId', 'example.com'),
        function (RouteBuilderRegistry $routes) {
            // This route's path will use the group's path
            $routes->map('GET', '')
                ->toMethod('CourseController', 'getCourseById');

            $routes->map('GET', '/professors')
                ->toMethod('CourseController', 'getCourseProfessors');
        }
    );
};
```

This creates two routes with a host suffix of `example.com` and a route prefix of `users/` (`example.com/courses/:courseId` and `example.com/courses/:courseId/professors`).  `RouteGroupOptions::__construct()` accepts the following parameters:

* `string $pathTemplate`
    * The path for routes in this group ([read about syntax](#route-variables))
    * This value is prefixed to the paths of all routes within the group
* `string|null $hostTemplate` (optional)
    * The optional host template for routes in this group  ([read about syntax](#route-variables))
    * This value is suffixed to the hosts of all routes within the group
* `bool $isHttpsOnly` (optional)
    * Whether or not the routes in this group are HTTPS-only
* `array $attributes` (optional)
    * The mapping of route attribute names => values
    * These attribute can be used with [custom constraint](#custom-constraints) matching
* `MiddlewareBinding[] $middleware` (optional)
    * The list of middleware bindings for routes in this group

It is possible to nest route groups.

<h1 id="custom-constraints">Custom Constraints</h1>

Sometimes, you might find it useful to add some custom logic for matching routes.  For example, you might want to add API versioning to your routes to force your API version header to match a version specified on the routes.  To do so, you'd use route "attributes" and a route constraint.  Route constraints implement `IRouteConstraint`, and are easy to make:

```php
use Opulence\Routing\Matchers\Constraints\IRouteConstraint;

class ApiVersionConstraint implements IRouteConstraint
{
    public function isMatch(string $host, string $path, array $headers, Route $route) : bool
    {
        $attributes = $route->getAttributes();

        if (!isset($attributes['API-VERSION'])) {
            return false;
        }

        return $headers['API-VERSION'] === $attributes['API-VERSION'];
    }
}
```

Next, we need to specify the API version attribute in our routes:

```php
$routesCallback = function (RouteBuilderRegistry $routes) {
    // This route will require an API-VERSION value of 'v1.0'
    $routes->map('GET', 'comments')
        ->toMethod('CommentController', 'getAllComments')
        ->withAttribute('API-VERSION', 'v1.0');
};
```

Finally, we need to let the route matcher know to use our `ApiVersionConstraint`:

```php
$routeMatcher = new RouteMatcher($routeFactory->createRoutes(), [new ApiVersionConstraint()]);
$matchedRoute = $routeMatcher->match(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['HTTP_HOST'],
    $_SERVER['REQUEST_URI'],
    $headers
);
```

If you plan on adding many attributes to your routes, use `RouteBuilder::withManyAttributes()`.

<h2 id="getting-php-headers">Getting Headers in PHP</h2>

PHP is irritatingly difficult to extract headers from `$_SERVER`.  If you're using a library/framework to grab headers, then use that.  Otherwise, you can use the `HeaderParser`:

```php
use Opulence\Routing\Matchers\Requests\HeaderParser;

$headers = (new HeaderParser)->parseHeaders($_SERVER);
```

<h1 id="route-variable-rules">Route Variable Rules</h2>

You can enforce certain rules to pass before matching on a route.  These rules come after variables, and must be enclosed in parentheses.  For example, if you want an integer to fall between two values, you can specify a route of

```php
:month(int,min(1),max(12))
```

> **Note:** If a rule does not require any parameters, then the parentheses after the rule slug are optional.

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

<h2 id="making-your-own-custom-rules">Making Your Own Custom Rules</h2>

You can register your own rule by implementing `IRule`.  Let's make a rule that enforces a certain minimum string length:

```php
use Opulence\Routing\Matchers\UriTemplates\Rules\IRule;

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
use Opulence\Routing\Matchers\UriTemplates\Rules\RuleFactory;
use Opulence\Routing\Matchers\UriTemplates\Rules\RuleFactoryRegistrant;

// Register some built-in rules to our factory
$ruleFactory = (new RuleFactoryRegistrant)->registerRuleFactories(new RuleFactory);

// Register our custom rule
$ruleFactory->registerRuleFactory(MinLengthRule::getSlug(), function (int $minLength) {
    return new MinLengthRule($minLength);
});
```

Finally, register this rule factory with the URI template compiler:

```php
use Opulence\Routing\Matchers\Builders\RouteBuilderRegistry;
use Opulence\Routing\Matchers\Caching\FileRouteCache;
use Opulence\Routing\Matchers\RuleFactory;
use Opulence\Routing\Matchers\UriTemplates\Compilers\UriTemplateCompiler;

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

<h1 id="caching">Caching</h1>

To speed up the compilation of your routes, Opulence supports caching (`FileRouteCache` is enabled by default).  If you're actively developing and adding new routes, it's best not to enable caching, which can be done by passing `null` into the `RouteFactory`:

```php
$routeFactory = new RouteFactory($routesCallback, null);
```

If you want to enable caching for a particular environment, you could do so:

```php
// Let's say that your environment name is stored in an environment var named 'ENV_NAME'
$routeCache = getenv('ENV_NAME') === 'production' ? new FileRouteCache('/tmp/routes.cache') : null;
$routeFactory = new RouteFactory($routesCallback, $routeCache);
```

<h1 id="micro-library">Micro-Library</h1>

If you don't want to use the route builders, and instead would rather create the routes yourself, you can.  Here's a complete example of how:

```php
use Opulence\Routing\Matchers\ClosureRouteAction;
use Opulence\Routing\Matchers\Route;
use Opulence\Routing\Matchers\RouteCollection;
use Opulence\Routing\Matchers\RouteMatcher;
use Opulence\Routing\Matchers\RouteNotFoundException;
use Opulence\Routing\Matchers\UriTemplates\UriTemplate;

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

In your `UriTemplate`, use regex capturing groups to grab any variables from the route.  Then, map the capturing groups to a list of variable names (order is important).  In the above example, `(\d+)` would map to a route variable named `userId`.