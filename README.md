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
    1. [Middleware Attributes](#middleware-attributes)
5. [Grouping Routes](#grouping-routes)
6. [Custom Constraints](#custom-constraints)
    1. [Getting Headers in PHP](#getting-php-headers)
7. [Route Variable Rules](#route-variable-rules)
    1. [Built-In Rules](#built-in-rules)
    2. [Making Your Own Custom Rules](#making-your-own-custom-rules)
8. [Caching](#caching)
    1. [Route Caching](#route-caching)
    2. [Regex Caching](#regex-caching)
9. [Micro-Library](#micro-library)

<h1 id="introduction">Introduction</h1>

This library is a route matching library.  In other words, it lets you map URIs to actions, and attempts to match an input request to ones of those routes.

<h2 id="why-use-this-library">Why Use This Library?</h2>

There are so many routing libraries out there.  Why use this one?  Well, there are a few reasons:

* It isn't coupled to _any_ library/framework
* It supports things that other route matching libraries do not support, like:
    * [Binding framework-agnostic middleware to routes](#binding-middleware)
    * [The ability to add custom matching rules on route variables](#route-variable-rules)
    * [The ability to match on header values](#custom-constraints), which makes things like versioning your routes a cinch
    * [Binding controller methods and closures to the route action](#route-actions)
* It is fast
    * With 100 routes with 9 route variables each, it can match any route in less than 1ms
* Its [fluent syntax](#route-builders) keeps you from having to memorize how to set up config arrays
* It supports [matching hosts](#route-builders)
* It is built to support the latest PHP 7.1 features

> **Note:** This is *not* a route dispatching library.  This library does not call controllers or closures on the matched route.  Why?  Usually, such actions are tightly coupled to an HTTP library or to a framework.  By not dispatching the matched route, you're free to use the library/framework of your choice, while still getting the benefits of performance and fluent syntax.

<h2 id="installation">Installation</h2>

This library requires PHP 7.1 and above.  It can be installed via <a href="https://getcomposer.org/" target="_blank">Composer</a> by including the following in your _composer.json_:
```
"opulence/route-matcher": "1.0.*@dev"
```

<h1 id="basic-usage">Basic Usage</h1>

Out of the box, this library provides a fluent syntax to help you build your routes.  Let's look at a working example.

First, let's import the namespaces and define our routes:

```php
use Opulence\Routing\Matchers\Builders\RouteBuilderRegistry;
use Opulence\Routing\Matchers\Caching\FileRouteCache;
use Opulence\Routing\Matchers\Regexes\Caching\FileGroupRegexCache;
use Opulence\Routing\Matchers\Regexes\GroupRegexFactory;
use Opulence\Routing\Matchers\{RouteFactory, RouteMatcher, RouteNotFoundException};

$routesCallback = function (RouteBuilderRegistry $routes) {
    $routes->map('GET', 'books/:bookId')
        ->toMethod(BookController::class, 'getBooksById')
        ->withMiddleware(AuthMiddleware::class);
};
```

Next, let's set up some factories to build your routes:

```php
$routeFactory = new RouteFactory(
    $routesCallback,
    new FileRouteCache('/tmp/routes.cache')
);
$regexFactory = new GroupRegexFactory(
    $routeFactory->createRoutes(),
    new FileGroupRegexCache('/tmp/regexes.cache')
);
```

Finally, let's find a matching route:

```php
try {
    $routeMatcher = new RouteMatcher($regexFactory->createRegexes());
    $matchedRoute = $routeMatcher->match(
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

Using our example, if we hit _example.com/books/123_, then `$matchedRoute` would be an instance of `MatchedRoute`.  Grabbing the matched controller info is as simple as:

```php
$matchedRoute->getAction()->getControllerName(); // "BookController"
$matchedRoute->getAction()->getMethodName(); // "getBooksById"
```

To get the [route variables](#route-variables), call:

```php
$matchedRoute->getRouteVars(); // ["bookId" => "123"]
```

To get the [middleware bindings](#binding-middleware), call:

```php
$matchedRoute->getMiddlewareBindings();
```

> **Note:** If you're using another HTTP library (eg Opulence, Symfony, or Laravel) in your application, it's better to use their methods to get the request method, host, and URI.  They account for things like trusted proxies as well as provide more robust handling of certain request headers.

<h2 id="route-variables">Route Variables</h2>

Opulence provides a simple syntax for your URIs.  To capture variables in your route, use `:varName`, eg:

```php
users/:userId/profile
```

If you want to specify a default value, then you'd write:

```php
users/:userId=me/profile
```

If you'd like to use [rules](#route-variable-rules), then put them in parentheses after the variable:
```php
:varName(rule1,rule2(param1,param2))
```

<h2 id="optional-route-parts">Optional Route Parts</h2>

If part of your route is optional, then surround it with brackets.  For example, the following will match both _archives/2017_ and _archives/2017/7_:
```php
archives/:year[/:month]
```

Optional route parts can be nested:

```php
archives/:year[/:month[/:day]]
```

This would match _archives/2017_, _archives/2017/07_, and _archives/2017/07/24_.

<h2 id="route-builders">Route Builders</h2>

Route builders give you a fluent syntax for mapping your routes to closures or controller methods.  They also let you [bind any middleware](#binding-middleware) classes and properties to the route.  To add a route builder, call `RouteBuilderRegistry::map()`, which accepts the following parameters:

* `string|array $httpMethods`
    * The HTTP method or list of methods to match on (eg `'GET'` or `['POST', 'GET']`)
* `string $pathTemplate`
    * The path for this route ([read about syntax](#route-variables))
* `string|null $hostTemplate` (optional)
    * The optional host template for this route  ([read about syntax](#route-variables))
* `bool $isHttpsOnly` (optional)
    * Whether or not this route is HTTPS-only

`RouteBuilderRegistry::map()` returns an instance of `RouteBuilder`.


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

To determine the type of action (controller method or closure) the matched route uses, check `RouteAction::usesClosure()`:

```php
if ($matchedRoute->getAction()->usesClosure()) {
    $closure = $matchedRoute->getAction()->getClosure();
} else {
    $controllerName = $matchedRoute->getAction()->getControllerName();
    $methodName = $matchedRoute->getAction()->getMethodName();
}
```

<h1 id="binding-middleware">Binding Middleware</h1>

Middleware are a great way to modify both the request and the response on an endpoint.  Opulence lets you define middleware on your endpoints without binding you to any particular library/framework's middleware implementations.

To bind a single middleware class to your route, call:

```php
$routes->map('GET', 'foo')
    ->toMethod(MyController::class, 'myMethod')
    ->withMiddleware(FooMiddleware::class);
```

To bind many middleware classes, call:

```php
$routes->map('GET', 'foo')
    ->toMethod(MyController::class, 'myMethod')
    ->withManyMiddleware([
        FooMiddleware::class,
        BarMiddleware::class
    ]);
```

Under the hood, these class names get converted to instances of `MiddlewareBinding`.  

<h2 id="middleware-attributes">Middleware Attributes</h2>

Some frameworks, such as Opulence and Laravel, let you bind attributes to middleware.  For example, if you have an `AuthMiddleware`, but need to bind the user role that's necessary to access that route, you might want to pass in the required user role.  Here's how you can do it:

```php
$routes->map('GET', 'foo')
    ->toMethod(MyController::class, 'myMethod')
    ->withMiddleware(AuthMiddleware::class, ['role' => 'admin']);

// Or

$routes->map('GET', 'foo')
    ->toMethod(MyController::class, 'myMethod')
    ->withManyMiddleware([
        new MiddlewareBinding(AuthMiddleware::class, ['role' => 'admin']),
        // Other middleware...
    ]);
```

Here's how you can grab the middleware on a matched route:

```php
foreach ($matchedRoute->getMiddlewareBindings() as $middlewareBinding) {
    $middlewareBinding->getClassName(); // "AuthMiddleware"
    $middlewareBinding->getAttributes(); // ["role" => "admin"]
}
```

<h1 id="grouping-routes">Grouping Routes</h1>

Often times, a lot of your routes will share similar properties, such as hosts and paths to match on, or middleware.  You can group these routes together using `RouteBuilderRegistry::group()` and specifying the options to apply to all routes within the group:

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
                ->toMethod(CourseController::class, 'getCourseProfessors');
        }
    );
};
```

This creates two routes with a host suffix of _example.com_ and a route prefix of _users/_ (`example.com/courses/:courseId` and `example.com/courses/:courseId/professors`).  `RouteGroupOptions::__construct()` accepts the following parameters:

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

Sometimes, you might find it useful to add some custom logic for matching routes.  For example, let's say your app sends an API version header, and you want to match an endpoint that supports that version.  You could do this by using a route "attribute" and a route constraint.  Let's create some routes that have the same path, but support different versions of the API:

```php
$routesCallback = function (RouteBuilderRegistry $routes) {
    // This route will require an API-VERSION value of 'v1.0'
    $routes->map('GET', 'comments')
        ->toMethod('CommentController', 'getAllComments')
        ->withAttribute('API-VERSION', 'v1.0');

    // This route will require an API-VERSION value of 'v2.0'
    $routes->map('GET', 'comments')
        ->toMethod(CommentController::class, 'getAllComments')
        ->withAttribute('API-VERSION', 'v2.0');
};
```

> **Note:** If you plan on adding many attributes to your routes, use `RouteBuilder::withManyAttributes()`.

Now, let's add a route constraint to match the "API-VERSION" header to the attribute on our route:

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

Finally, we need to let the route matcher know to use our `ApiVersionConstraint`:

```php
$routeMatcher = new RouteMatcher($regexFactory->createRegexes(), [new ApiVersionConstraint()]);
$matchedRoute = $routeMatcher->match(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['HTTP_HOST'],
    $_SERVER['REQUEST_URI'],
    $headers
);
```

If we hit _/comments_ with an "API-VERSION" header value of "v2.0", we'd match the second route in our example.

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
* `between($min, $max, bool $isInclusive = true)`
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
    private $minLength = 0;

    public function __construct(int $minLength)
    {
        $this->minLength = $minLength;
    }

    public static function getSlug() : string
    {
        return 'minLength';
    }

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
use Opulence\Routing\Matchers\RouteFactory;
use Opulence\Routing\Matchers\UriTemplates\Compilers\UriTemplateCompiler;

$routesCallback = function (RouteBuilderRegistry $routes) {
    $routes->map('parts/:serialNumber(minLength(6))')
        ->toMethod(PartController::class, 'getPartBySerialNumber');
};
$routeFactory = new RouteFactory(
    $routesCallback,
    new FileRouteCache('/tmp/routes.cache'),
    new RouteBuilderRegistry(new UriTemplateCompiler($ruleFactory))
);
```

Our route will now enforce a serial number with minimum length 6.

<h1 id="caching">Caching</h1>

<h2 id="route-caching">Route Caching</h2>

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

<h2 id="regex-caching">Regex Caching</h2>

The route matcher processes groups of routes at a time while trying to find a match.  This requires combining those routes' URI templates' regexes.  This process is repetitive, and only really needs to happen once.  So, Opulence provides a cache (`FileGroupRegexCache` is enabled by default) to make this process faster.  This cache is passed in as the second parameter to `GroupRegexFactory`:

```php
$regexFactory = new GroupRegexFactory($routes, new FileGroupRegexCache('/tmp/regexes.cache'));
```

Similar to the [route cache](#route-caching), you can optionally pass in `null` if you do not wish to cache the regexes.

> **Note:** If you flush the route cache, you should also flush the regex cache (and vice versa).

<h1 id="micro-library">Micro-Library</h1>

If you don't want to use the route builders, and instead would rather create the routes yourself, you can.  Here's a complete example of how:

```php
use Opulence\Routing\Matchers\ClosureRouteAction;
use Opulence\Routing\Matchers\Regexes\GroupRegexFactory;
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
        return 'Hello, world';
    })
));
$regexFactory = new GroupRegexFactory($routes);

// Find a matching route
try {
    $routeMatcher = new RouteMatcher($regexFactory->createRegexes());
    $matchedRoute = $routeMatcher->match(
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