<h1>Router</h1>

[![Build Status](https://travis-ci.com/aphiria/router.svg)](https://travis-ci.com/aphiria/router)
[![Latest Stable Version](https://poser.pugx.org/aphiria/router/v/stable.svg)](https://packagist.org/packages/aphiria/router)
[![Latest Unstable Version](https://poser.pugx.org/aphiria/router/v/unstable.svg)](https://packagist.org/packages/aphiria/router)
[![License](https://poser.pugx.org/aphiria/router/license.svg)](https://packagist.org/packages/aphiria/router)

> **Note:** This library is still in development.

<h1>Table of Contents</h1>

1. [Introduction](#introduction)
    1. [Why Use This Library?](#why-use-this-library)
    2. [Installation](#installation)
2. [Basic Usage](#basic-usage)
    1. [Route Variables](#route-variables)
    2. [Optional Route Parts](#optional-route-parts)
    3. [Route Builders](#route-builders)
3. [Route Actions](#route-actions)
4. [Binding Middleware](#binding-middleware)
    1. [Middleware Attributes](#middleware-attributes)
5. [Grouping Routes](#grouping-routes)
6. [Custom Constraints](#custom-constraints)
    1. [Example - Versioned API](#versioned-api-example)
    2. [Getting Headers in PHP](#getting-php-headers)
7. [Route Variable Rules](#route-variable-rules)
    1. [Built-In Rules](#built-in-rules)
    2. [Making Your Own Custom Rules](#making-your-own-custom-rules)
8. [Caching](#caching)
9. [Matching Algorithm](#matching-algorithm)

<h1 id="introduction">Introduction</h1>

This library is a routing library.  In other words, it lets you map URIs to actions, and attempts to match an input request to ones of those routes.

<h2 id="why-use-this-library">Why Use This Library?</h2>

There are so many routing libraries out there.  Why use this one?  Well, there are a few reasons:

* It isn't coupled to _any_ library/framework
* It supports things that other route matching libraries do not support, like:
    * [Binding framework-agnostic middleware to routes](#binding-middleware)
    * [The ability to add custom matching rules on route variables](#route-variable-rules)
    * [The ability to match on header values](#custom-constraints), which makes things like versioning your routes a cinch
    * [Binding controller methods and closures to the route action](#route-actions)
* It is fast
    * With 400 routes, it takes ~0.0025ms to match any route (**~200% faster than FastRoute**)
    * The speed is due to the unique [trie-based matching algorithm](#matching-algorithm)
* Its [fluent syntax](#route-builders) keeps you from having to memorize how to set up config arrays
* It is built to support the latest PHP 7.3 features

> **Note:** This is *not* a route dispatching library.  This library does not call controllers or closures on the matched route.  Why?  Usually, such actions are tightly coupled to an HTTP library or to a framework.  By not dispatching the matched route, you're free to use the library/framework of your choice, while still getting the benefits of performance and fluent syntax.

<h2 id="installation">Installation</h2>

This library requires PHP 7.3 and above.  It can be installed via <a href="https://getcomposer.org/" target="_blank">Composer</a> by including the following in your _composer.json_:

```bash
"aphiria/router": "1.0.*@dev"
```

<h1 id="basic-usage">Basic Usage</h1>

Out of the box, this library provides a fluent syntax to help you build your routes.  Let's look at a working example.

First, let's import the namespaces and define our routes:

```php
use Aphiria\Routing\Builders\RouteBuilderRegistry;
use Aphiria\Routing\Matchers\Trees\{TrieFactory, TrieRouteMatcher};

// Register the routes
$routes = new RouteBuilderRegistry();
$routes->map('GET', '/books/:bookId')
    ->toMethod(BookController::class, 'getBooksById')
    ->withMiddleware(AuthMiddleware::class);

// Set up the route matcher
$trieFactory = new TrieFactory($routes, null);
$routeMatcher = new TrieRouteMatcher($trieFactory->createTree());

// Finally, let's find a matching route
$result = $routeMatcher->matchRoute(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['HTTP_HOST'],
    $_SERVER['REQUEST_URI']
);
```

Let's say the request was `GET /books/123`.  You can check if a match was found by calling:

```php
if ($result->matchFound) {
    // ...
}
```

Grabbing the matched controller info is as simple as:

```php
$result->route->action->controllerName; // "BookController"
$result->route->action->methodName; // "getBooksById"
```

To get the [route variables](#route-variables), call:

```php
$result->routeVariables; // ["bookId" => "123"]
```

To get the [middleware bindings](#binding-middleware), call:

```php
$result->route->middlewareBindings;
```

If `$result->methodIsAllowed` is `false`, you can return a 405 response with a list of allowed methods:

```php
header('Allow', implode(', ', $result->allowedMethods));
```

> **Note:** If you're using another HTTP library (eg <a href="https://github.com/aphiria/net" target="_blank">Aphiria</a>, Symfony, or Laravel) in your application, it's better to use their methods to get the request method, host, and URI.  They account for things like trusted proxies as well as provide more robust handling of certain request headers.

<h2 id="route-variables">Route Variables</h2>

Aphiria provides a simple syntax for your URIs.  To capture variables in your route, use `:varName`, eg:

```php
users/:userId/profile
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

Aphiria supports mapping routes to both controller methods and to closures:

```php
// Map to a controller method
$routes->map('GET', 'users/:userId')
    ->toMethod(UserController::class, 'getUserById');

// Map to a closure
$routes->map('GET', 'users/:userId/name')
    ->toClosure(function () {
        // Handle the request...
    });
```

To determine the type of action (controller method or closure) the matched route uses, check `RouteAction::usesMethod()`.

<h1 id="binding-middleware">Binding Middleware</h1>

Middleware are a great way to modify both the request and the response on an endpoint.  Aphiria lets you define middleware on your endpoints without binding you to any particular library/framework's middleware implementations.

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

Some frameworks, such as Aphiria and Laravel, let you bind attributes to middleware.  For example, if you have an `AuthMiddleware`, but need to bind the user role that's necessary to access that route, you might want to pass in the required user role.  Here's how you can do it:

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
foreach ($result->middlewareBindings as $middlewareBinding) {
    $middlewareBinding->className; // "AuthMiddleware"
    $middlewareBinding->attributes; // ["role" => "admin"]
}
```

<h1 id="grouping-routes">Grouping Routes</h1>

Often times, a lot of your routes will share similar properties, such as hosts and paths to match on, or middleware.  You can group these routes together using `RouteBuilderRegistry::group()` and specifying the options to apply to all routes within the group:

```php
use Aphiria\Routing\Builders\RouteGroupOptions;

$routes->group(
    new RouteGroupOptions('courses/:courseId', 'example.com'),
    function (RouteBuilderRegistry $routes) {
        // This route's path will use the group's path
        $routes->map('GET', '')
            ->toMethod(CourseController::class, 'getCourseById');

        $routes->map('GET', '/professors')
            ->toMethod(CourseController::class, 'getCourseProfessors');
    }
);
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
* `IRouteConstraint[] $constraints` (optional)
* `array $attributes` (optional)
    * The mapping of route attribute names => values
    * These attribute can be used with [custom constraint](#custom-constraints) matching
* `MiddlewareBinding[] $middleware` (optional)
    * The list of middleware bindings for routes in this group

It is possible to nest route groups.

<h1 id="custom-constraints">Custom Constraints</h1>

Sometimes, you might find it useful to add some custom logic for matching routes.  This could involve enforcing anything from only allowing certain HTTP methods for a route (eg `HttpMethodRouteConstraint`) or only allowing HTTPS requests to a particular endpoint.  Let's go into some concrete examples...

<h2 id="versioned-api-example">Example - Versioned API</h2>

Let's say your app sends an API version header, and you want to match an endpoint that supports that version.  You could do this by using a route "attribute" and a route constraint.  Let's create some routes that have the same path, but support different versions of the API:

```php
// This route will require an API-VERSION value of 'v1.0'
$routes->map('GET', 'comments')
    ->toMethod(CommentController::class, 'getAllComments1_0')
    ->withAttribute('API-VERSION', 'v1.0')
    ->withConstraint(new ApiVersionConstraint);

// This route will require an API-VERSION value of 'v2.0'
$routes->map('GET', 'comments')
    ->toMethod(CommentController::class, 'getAllComments2_0')
    ->withAttribute('API-VERSION', 'v2.0')
    ->withConstraint(new ApiVersionConstraint);
```

> **Note:** If you plan on adding many attributes or constraints to your routes, use `RouteBuilder::withManyAttributes()` and `RouteBuilder::withManyConstraints()`, respectively.

Now, let's add a route constraint to match the "API-VERSION" header to the attribute on our route:

```php
use Aphiria\Routing\Matchers\Constraints\IRouteConstraint;
use Aphiria\Routing\Matchers\MatchedRouteCandidate;

class ApiVersionConstraint implements IRouteConstraint
{
    public function passes(
        MatchedRouteCandidate $matchedRouteCandidate,
        string $host,
        string $path,
        array $headers
    ): bool {
        $attributes = $matchedRouteCandidate->route->attributes;

        if (!isset($attributes['API-VERSION'])) {
            return false;
        }

        return array_search($attributes['API-VERSION'], $headers['API-VERSION']) !== false;
    }
}
```

If we hit _/comments_ with an "API-VERSION" header value of "v2.0", we'd match the second route in our example.

<h2 id="getting-php-headers">Getting Headers in PHP</h2>

PHP is irritatingly difficult to extract headers from `$_SERVER`.  If you're using a library/framework to grab headers, then use that.  Otherwise, you can use the `HeaderParser`:

```php
use Aphiria\Routing\Requests\HeaderParser;

$headers = (new HeaderParser)->parseHeaders($_SERVER);
```

<h1 id="route-variable-rules">Route Variable Rules</h2>

You can enforce certain rules to pass before matching on a route.  These rules come after variables, and must be enclosed in parentheses.  For example, if you want an integer to fall between two values, you can specify a route of

```php
:month(int,min(1),max(12))
```

> **Note:** If a rule does not require any parameters, then the parentheses after the rule slug are optional.

<h2 id="built-in-rules">Built-In Rules</h2>

The following rules are built-into Aphiria:

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
use Aphiria\Routing\Matchers\Rules\IRule;

class MinLengthRule implements IRule
{
    private $minLength = 0;

    public function __construct(int $minLength)
    {
        $this->minLength = $minLength;
    }

    public static function getSlug(): string
    {
        return 'minLength';
    }

    public function passes($value): bool
    {
        return mb_strlen($value) >= $this->minLength;
    }
}
```

Let's register our rule with the rule factory:

```php
use Aphiria\Routing\Matchers\Rules\{RuleFactory, RuleFactoryRegistrant};

// Register some built-in rules to our factory
$ruleFactory = (new RuleFactoryRegistrant)->registerRuleFactories(new RuleFactory);

// Register our custom rule
$ruleFactory->registerRuleFactory(MinLengthRule::getSlug(), function (int $minLength) {
    return new MinLengthRule($minLength);
});
```

Finally, register this rule factory with the trie compiler:

```php
use Aphiria\Routing\Builders\RouteBuilderRegistry;
use Aphiria\Routing\Matchers\Trees\{TrieFactory, TrieRouteMatcher};

$routes = new RouteBuilderRegistry();
$routes->map('parts/:serialNumber(minLength(6))')
    ->toMethod(PartController::class, 'getPartBySerialNumber');

$trieCompiler = new TrieCompiler($ruleFactory);
$trieFactory = new TrieFactory($routes, null, $trieCompiler);
$routeMatcher = new TrieRouteMatcher($trieFactory->createTree());
```

Our route will now enforce a serial number with minimum length 6.

<h1 id="caching">Caching</h1>

To speed up the compilation of your route trie, Aphiria supports caching (`FileTrieCache` is provided by default).  If you're actively developing and adding new routes, it's best not to enable caching, which can be done by passing `null` into the `TrieFactory`:

```php
$trieFactory = new TrieFactory($routes, null);
```

If you want to enable caching for a particular environment, you could do so:

```php
// Let's say that your environment name is stored in an environment var named 'ENV_NAME'
$trieCache = getenv('ENV_NAME') === 'production' ? new FileTrieCache('/tmp/trie.cache') : null;
$trieFactory = new TrieFactory($routes, $trieCache);
```

<h1 id="matching-algorithm">Matching Algorithm</h1>

Rather than the typical regex approach to route matching, we decided to go with a <a href="https://en.wikipedia.org/wiki/Trie" target="_blank">trie-based</a> approach.  Each node maps to a segment in the path, and could either contain a literal or a variable value.  We try to proceed down the tree to match what's in the request URI, always giving preference to literal matches over variable ones, even if variable segments are declared first in the routing config.  This logic not only applies to the first segment, but recursively to all subsequent segments.  The benefit to this approach is that it doesn't matter what order routes are defined.  Additionally, literal segments use simple hash table lookups.  What determines performance is the length of a path and the number of variable segments.

The matching algorithm goes as follows:

1. Incoming request data is passed to a `TrieRouteMatcher::matchRoute()`, which loops through each segment of the URI path and proceeds only if there is either a literal or variable match in the URI tree
    * If there's a match, then we scan all child nodes against the next segment of the URI path and repeat step 1 until we don't find a match or we've matched the entire URI path
    * `TrieRouteMatcher::matchRoute()` uses <a href="http://php.net/manual/en/language.generators.syntax.php" target="_blank">generators</a> so we only descend the URI tree as many times as we need to find a match candidate
2. If the match candidate passes constraint checks (eg HTTP method constraints), then it's our matching route, and we're done.  Otherwise, repeat step 1, which will yield the next possible match candidate.