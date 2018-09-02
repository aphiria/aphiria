<h1>API</h1>

> **Note:** This library is still in development.

<h1>Table of Contents</h1>

1. [Introduction](#introduction)
    1. [Installation](#installation)
2. [Controllers](#controllers)
    1. [Parameter Resolution](#parameter-resolution)
    2. [Closure Controllers](#closure-controllers)
    3. [Controller Dependencies](#controller-dependencies)
3. [Middleware](#middleware)
    1. [Manipulating the Request](#manipulating-the-request)
    2. [Manipulating the Response](#manipulating-the-response)
    3. [Middleware Attributes](#middleware-attributes)
4. [Request Handlers](#request-handlers)
5. [Exception Handling](#exception-handling)
    1. [Exception Response Factories](#exception-response-factories)
    2. [Logging](#logging)

<h1 id="introduction">Introduction</h1>

The API library makes it simpler for you to get your application's API up and running.  It acts as the entry point into your application, and takes advantage of several of Opulence's other libraries to handle things like route matching and content negotiation.

<h2 id="installation">Installation</h2>

You can install this library by including the following package name in your _composer.json_:

```
"opulence/api": "1.0.*"
```

<h1 id="controllers">Controllers</h1>

Your controllers can either extend `Controller` or be a [`Closure`](#closure-controllers).  Let's say you wanted to create a user.  Simple:

```php
class UserController extends Controller
{
    // ...
    
    public function createUser(User $user): User
    {
        $this->userRepository->addUser($user);

        return $user;
    }
}
```

Opulence will see the `User` parameter and [automatically deserialize the request body to an instance of `User`](#parameter-resolution).  It will also detect that a `User` object was returned, and create a 200 response whose body is the serialized user object.  Here's the nice part - your models can be POPOs, and Opulence will automatically know how to (de)serialize them.  It uses <a href="https://github.com/opulencephp/net#content-negotiation" target="_blank">content negotiation</a> to determine the media type to serialize to (eg JSON).

You can also be a bit more explicit and return a response yourself.  For example, the following controller method is functionally identical to the previous example:

```php
class UserController extends Controller
{
    // ...
    
    public function createUser(User $user): IHttpResponseMessage
    {
        $this->userRepository->addUser($user);

        return $this->ok($user);
    }
}
```

The `ok()` helper method uses a `NegotiatedResponseFactory` to build a response using the current request and <a href="https://github.com/opulencephp/net#content-negotiation" target="_blank">content negotiation</a>.  You can pass in a POPO as the response body, and the factory will use content negotiation to determine how to serialize it.

The following helper methods come bundled with `Controller`:

* `badRequest()`
* `conflict()`
* `created()`
* `forbidden()`
* `found()`
* `internalServerError()`
* `movedPermanently()`
* `noContent()`
* `notFound()`
* `ok()`
* `unauthorized()`

If your controller method has a `void` return type, a 204 "No Content" response will be created automatically.

If you need access to the current request, use `$this->request` within your controller method.

<h3 id="headers">Headers</h3>

Setting headers is simple, too:

```php
use Opulence\Net\Http\HttpHeaders;

class UserController extends Controller
{
    // ...
    
    public function getUserById(int $userId): IHttpResponseMessage
    {
        $user = $this->userRepository->getUserById($userId);
        $headers = new HttpHeaders();
        $headers->add('Cache-Control', 'no-cache');
        
        return $this->ok($user, $headers);
    }
}
```

<h2 id="parameter-resolution">Parameter Resolution</h2>

Your controller methods will frequently need to do things like deserialize the request body or read route/query string values.  Opulence simplifies this process enormously by allowing your method signatures to be expressive.  For example, if you specify any object type hint, it will automatically deserialize the request body to any POPO:

```php
class UserController extends Controller
{
    // ...
    
    public function createUser(User $user): IHttpResponseMessage
    {
        $this->userRepository->addUser($user);
        
        return $this->created();
    }
}
```

This works for any media type (eg JSON) that you've registered to your <a href="https://github.com/opulencephp/net#content-negotiation" target="_blank">content negotiator</a>.

Opulence also supports resolving scalar values in your controller methods.  It will scan route variables, and then, if no matches are found, the query string for scalar parameters.  For example, this method will grab `includeDeletedUsers` from the query string and cast it to a `bool`:

```php
class UserController extends Controller
{
    // ...
    
    // Assume path and query string is "users?includeDeletedUsers=1"
    public function getAllUsers(bool $includeDeletedUsers): array
    {
        return $this->userRepository->getAllUsers($includeDeletedUsers);
    }
}
```

Nullable parameters and parameters with default values are also supported.

<h3 id="arrays-in-request-body">Arrays in Request Body</h3>

Request bodies might contain an array of values.  Because PHP doesn't support generics or typed arrays, you cannot use type-hints alone to deserialize arrays of values.  However, it's still easy to do:

```php
class UserController extends Controller
{
    // ...

    public function createManyUsers(): IHttpResponseMessage
    {
        $users = $this->readRequestBodyAsArrayOfType(User::class);
        $this->userRepository->addManyUsers($users);
        
        return $this->created();
    }
}
```

<h2 id="closure-controllers">Closure Controllers</h2>

Sometimes, a controller class is overkill for a route that does very little.  In this case, you can use a `Closure` when defining your routes:

```php
 $routes->map('GET', 'ping')
    ->toClosure(function () {
        return $this->ok();
    });
```

Closures support the same [parameter resolution](#parameter-resolution) features as controller methods.  Here's the cool part - Opulence will bind an instance of `Controller` to your closure, which means you can use [all the methods](#controllers) available inside of `Controller` via `$this`.

<h2 id="controller-dependencies">Controller Dependencies</h2>

The API library provides support for auto-wiring your controllers.  In other words, it can scan your controllers' constructors for dependencies, resolve them, and then instantiate your controllers with those dependencies.  Dependency resolvers simply need to implement `IDependencyResolver`.  To make it easy for users of Opulence's DI container, you can use `ContainerDependencyResolver`.

Once you've instantiated your dependency resolver, pass it into your [request handler](#request-handlers) for auto-wiring.

<h1 id="middleware">Middleware</h1>

HTTP middleware are classes that sit in between the `RequestHandler` and `Controller`.  They manipulate the request and response to do things like authenticate users or enforce CSRF protection for certain routes.  They are executed in series in a pipeline.

Opulence uses dependency injection for type-hinted objects in a `Middleware` constructor.  So, if you need any objects in your `handle()` method, just specify them in the constructor.  Let's take a look at an example:

```php
namespace Project\Application\Http\Middleware;

use Closure;
use Opulence\Api\Middleware\IMiddleware;
use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;
use Opulence\Net\Http\Response;
use Project\Domain\Authentication\Authenticator;

class Authentication implements IMiddleware
{
    private $authenticator = null;

    // Inject any dependencies your middleware needs
    public function __construct(Authenticator $authenticator)
    {
        $this->authenticator = $authenticator;
    }

    // $next consists of the next middleware in the pipeline
    public function handle(IHttpRequestMessage $request, Closure $next): IHttpResponseMessage
    {
        if (!$this->authenticator->isLoggedIn()) {
            $headers = new HttpHeaders();
            $headers->add('Location', '/login');

            return new Response(301, $headers);
        }

        return $next($request);
    }
}
```

You can then bind the middleware to your route:

```php
$routes->map('POST', 'posts')
    ->toMethod(PostController::class, 'createPost')
    ->withMiddleware(Authentication::class);
```

Now, the `Authenticate` middleware will be run before the `createPost()` method is called.  If the user is not logged in, s/he'll be redirected to the login page.

> **Note:** If middleware does not specifically call the `$next` closure, none of the middleware after it in the pipeline will be run.

<h2 id="manipulating-the-request">Manipulating the Request</h2>

To manipulate the request before it gets to the controller, make changes to it before calling `$next($request)`:

```php
use Closure;
use Opulence\Api\Middleware\IMiddleware;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;

class RequestManipulator implements IMiddleware
{
    public function handle(IHttpRequestMessage $request, Closure $next): IHttpResponseMessage
    {
        // Do our work before returning $next($request)
        $request->getProperties()->add('Foo', 'bar');

        return $next($request);
    }
}
```

<h2 id="manipulating-the-response">Manipulating the Response</h2>

To manipulate the response after the controller has done its work, do the following:

```php
use Closure;
use Opulence\Api\Middleware\IMiddleware;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;

class ResponseManipulator implements IMiddleware
{
    public function handle(IHttpRequestMessage $request, Closure $next): IHttpResponseMessage
    {
        $response = $next($request);

        // Make our changes
        $response->getHeaders()->add('Foo', 'bar');

        return $response;
    }
}
```

<h2 id="middleware-attributes">Middleware Attributes</h2>

Occasionally, you'll find yourself wanting to pass primitive values to middleware to indicate something such as a required role to execute an action.  In these cases, your middleware should extend `Opulence\Api\Middleware\AttributeMiddleware`:

```php
use Closure;
use Opulence\Api\Middleware\AttributeMiddleware;
use Opulence\Net\Http\HttpException;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;

class RoleMiddleware extends AttributeMiddleware
{
    private $user;

    // Inject any dependencies your middleware needs
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle(IHttpRequestMessage $request, Closure $next): IHttpResponseMessage
    {
        // Attributes are available via $this->getAttribte()
        // You may pass in a second parameter as the default value if the attribute
        // was not found
        if (!$this->user->hasRole($this->getAttribute('role'))) {
            throw new HttpException(403);
        }

        return $next($request);
    }
}
```

To actually specify `role`, pass it into your route configuration:

```php
$routes->map('GET', 'foo')
    ->toMethod(MyController::class, 'myMethod')
    ->withMiddleware(RoleMiddleware::class, ['role' => 'admin']);
```

<h1 id="request-handlers">Request Handlers</h1>

A request handler simply takes in an HTTP request and returns a response.  It is capable of matching a route and sending the request and response through [middleware](#middleware) to the [controller](#controllers).

Configuring your API is easy - you just need to set up a few things:

* <a href="https://github.com/opulencephp/router#basic-usage" target="_blank">Routes</a>
* <a href="https://github.com/opulencephp/net#content-negotiation" target="_blank">Content negotiator</a>
* [Dependency resolver](#controller-dependencies)

Handling a request from beginning to end is simple:

```php
use Opulence\Api\Handlers\ControllerRequestHandler;
use Opulence\Net\Http\RequestFactory;
use Opulence\Net\Http\ResponseWriter;

// Assume your route matcher, dependency resolver, and content negotiator are already set
$requestHandler = new ControllerRequestHandler(
    $routeMatcher,
    $dependencyResolver,
    $contentNegotiator
);
$request = (new RequestFactory)->createRequestFromSuperglobals($_SERVER);
$response = $requestHandler->handle($request);
(new ResponseWriter)->writeResponse($response);
```

<h1 id="exception-handling">Exception Handling</h1>

Sometimes, your application is going to throw an unhandled exception or shut down unexpectedly.  When this happens, instead of showing an ugly PHP error, you can convert it to a nicely-formatted response.  To get set up, you can simply instantiate `ExceptionHandler` and register it with PHP:

```php
use Opulence\Api\Exceptions\ExceptionHandler;
use Opulence\Api\Exceptions\ExceptionResponseFactory;
use Opulence\Net\Http\ContentNegotiation\NegotiatedResponseFactory;

// Assume the content negotiator was already set up
$exceptionResponseFactory = new ExceptionResponseFactory(
    new NegotiatedResponseFactory($contentNegotiator)
);

$exceptionHandler = new ExceptionHandler($exceptionResponseFactory);
$exceptionHandler->registerWithPhp();
```

By default, `ExceptionHandler` will convert any exception to a 500 response and use <a href="https://github.com/opulencephp/net#content-negotiation" target="_blank">content negotiation</a> to determine the best format for the response body.  However, you can [customize your exception responses](#exception-response-factories).

<h2 id="exception-response-factories">Exception Response Factories</h2>

You might find yourself wanting to map a particular exception to a certain response.  In this case, you can use an exception response factory.  They are closures that take in the exception and the request, and return a response.

As an example, let's say that you want to return a 404 response when an `EntityNotFound` exception is thrown:

```php
use Opulence\Api\Exceptions\ExceptionResponseFactory;
use Opulence\Api\Exceptions\ExceptionResponseFactoryRegistry;
use Opulence\Net\Http\HttpStatusCodes;
use Opulence\Net\Http\Response;

// Register your custom exception response factories
$exceptionResponseFactoryRegistry = new ExceptionResponseFactoryRegistry();
$exceptionResponseFactoryRegistry->registerFactory(
    EntityNotFound::class,
    function (EntityNotFound $ex, ?IHttpRequestMessage $request) {
        return new Response(HttpStatusCodes::HTTP_NOT_FOUND);
    }
);

// Assume the content negotiator was already set up
$exceptionResponseFactory = new ExceptionResponseFactory(
    new NegotiatedResponseFactory($contentNegotiator),
    $exceptionResponseFactoryRegistry
);

// Add it to the exception handler
$exceptionHandler = new ExceptionHandler($exceptionResponseFactory);
$exceptionHandler->registerWithPhp();
```

That's it.  Now, whenever an unhandled `EntityNotFound` exception is thrown, your application will return a 404 response.  You can also register multiple exception factories at once.  Just pass in an array, keyed by exception type:

```php
$exceptionResponseFactoryRegistry->registerFactories([
    EntityNotFound::class => function (EntityNotFound $ex, ?IHttpRequestMessage $request) {
        return new Response(HttpStatusCodes::HTTP_NOT_FOUND);
    },
    // ...
]);
```

If you want to take advantage of automatic content negotiation, you can use a `NegotiatedResponseFactory` in your closure:

```php
use Opulence\Net\Http\ContentNegotiation\NegotiatedResponseFactory;

// Assume the content negotiator was already set up
$negotiatedResponseFactory = new NegotiatedResponseFactory($contentNegotiator);
// ...
$exceptionResponseFactoryRegistry->registerFactory(
    EntityNotFound::class,
    function (EntityNotFound $ex, ?IHttpRequestMessage $request) use ($negotiatedResponseFactory) {
        return $negotiatedResponseFactory->createResponse(
            $request,
            HttpStatusCodes::HTTP_NOT_FOUND,
            null,
            new SomeCustomMessage('Entity not found')
        );
    }
);
```

If an unhandled `EntityNotFound` exception was thrown, your exception factory will use content negotiation to serialize `SomeCustomMessage` in the response body.

<h2 id="logging">Logging</h2>

Unless you specify otherwise, a <a href="https://github.com/Seldaek/monolog" target="_blank">Monolog</a> logger to log all exceptions to the PHP error log.  However, you can override this with any PSR-3 logger:

```php
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;

$logger = new Logger('app');
$logger->pushHandler(new SyslogHandler());
$exceptionHandler = new ExceptionHandler(null, $logger);
$exceptionHandler->registerWithPhp();
```

If you don't want to log particular exceptions, you can specify them:

```php
$exceptionHandler = new ExceptionHandler(null, null, null, null, ['MyException']);
$exceptionHandler->registerWithPhp();
```

You can also control the level of PHP errors that are logged by specifying a bitwise value similar to what's in your _php.ini_:

```php
$exceptionHandler = new ExceptionHandler(null, null, E_ALL & ~E_NOTICE);
$exceptionHandler->registerWithPhp();
```