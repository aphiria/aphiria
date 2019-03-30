<h1>API</h1>

[![Build Status](https://travis-ci.com/aphiria/api.svg)](https://travis-ci.com/aphiria/api)
[![Latest Stable Version](https://poser.pugx.org/aphiria/api/v/stable.svg)](https://packagist.org/packages/aphiria/api)
[![Latest Unstable Version](https://poser.pugx.org/aphiria/api/v/unstable.svg)](https://packagist.org/packages/aphiria/api)
[![License](https://poser.pugx.org/aphiria/api/license.svg)](https://packagist.org/packages/aphiria/api)

> **Note:** This library is still in development.

<h1>Table of Contents</h1>

1. [Introduction](#introduction)
    1. [Installation](#installation)
2. [Controllers](#controllers)
    1. [Parameter Resolution](#parameter-resolution)
    2. [Parsing Request Data](#parsing-request-data)
    3. [Formatting Response Data](#formatting-response-data)
    4. [Closure Controllers](#closure-controllers)
    5. [Controller Dependencies](#controller-dependencies)
3. [Middleware](#middleware)
    1. [Configuring Middleware](#configuring-middleware)
4. [API Kernel](#api-kernel)
5. [Exception Handling](#exception-handling)
    1. [Customizing Exception Responses](#customizing-exception-responses)
    2. [Logging](#logging)

<h1 id="introduction">Introduction</h1>

The API library makes it simpler for you to get your application's API up and running.  It acts as the entry point into your application, and takes advantage of several of Aphiria's other libraries to handle things like route matching and content negotiation.

<h2 id="installation">Installation</h2>

You can install this library by including the following package name in your _composer.json_:

```bash
"aphiria/api": "1.0.*"
```

<h1 id="controllers">Controllers</h1>

Your controllers can either extend `Controller` or be a [`Closure`](#closure-controllers).  Let's say you needed an endpoint to create a user.  Simple:

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

Aphiria will see the `User` method parameter and [automatically deserialize the request body to an instance of `User`](#parameter-resolution) (which can be a POPO) using <a href="https://github.com/aphiria/net#content-negotiation" target="_blank">content negotiation</a>.  It will also detect that a `User` object was returned by the method, and create a 200 response whose body is the serialized user object.  It uses <a href="https://github.com/aphiria/net#content-negotiation" target="_blank">content negotiation</a> to determine the media type to (de)serialize to (eg JSON).

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

The `ok()` helper method uses a `NegotiatedResponseFactory` to build a response using the current request and <a href="https://github.com/aphiria/net#content-negotiation" target="_blank">content negotiation</a>.  You can pass in a POPO as the response body, and the factory will use content negotiation to determine how to serialize it.

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
use Aphiria\Net\Http\HttpHeaders;

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

Your controller methods will frequently need to do things like deserialize the request body or read route/query string values.  Aphiria simplifies this process enormously by allowing your method signatures to be expressive.  

<h3 id="request-body-parameters">Request Bodies</h3>

Object type hints are always assumed to be the request body, and can be automatically deserialized to any POPO:

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

This works for any media type (eg JSON) that you've registered to your <a href="https://github.com/aphiria/net#content-negotiation" target="_blank">content negotiator</a>.

<h3 id="uri-parameters">URI Parameters</h3>

Aphiria also supports resolving scalar parameters in your controller methods.  It will scan route variables, and then, if no matches are found, the query string for scalar parameters.  For example, this method will grab `includeDeletedUsers` from the query string and cast it to a `bool`:

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

Nullable parameters and parameters with default values are also supported.  If a query string parameter is optional, it _must_ be either nullable or have a default value.

<h3 id="arrays-in-request-body">Arrays in Request Body</h3>

Request bodies might contain an array of values.  Because PHP doesn't support generics or typed arrays, you cannot use type-hints alone to deserialize arrays of values.  However, it's still easy to do within your controller methods:

```php
class UserController extends Controller
{
    // ...

    public function createManyUsers(): IHttpResponseMessage
    {
        $users = $this->readRequestBodyAs(User::class . '[]');
        $this->userRepository->addManyUsers($users);
        
        return $this->created();
    }
}
```

<h2 id="parsing-request-data">Parsing Request Data</h2>

Your controllers might need to do more advanced reading of request data, such as reading cookies, reading multipart bodies, or determining the content type of the request.  To simplify this kind of work, an instance of `RequestParser` is set in your controller:

```php
class JsonPrettifierController extends Controller
{
    // ...

    public function prettifyJson(): IHttpResponseMessage
    {
        if (!$this->requestParser->isJson($this->request)) {
            return $this->badRequest();
        }
        
        $prettyJson = json_encode($this->request->getBody()->readAsString(), JSON_PRETTY_PRINT);
        $response = new Response(200, null, new StringBody($prettyJson));
        
        return $response;
    }
}
```

<h2 id="formatting-response-data">Formatting Response Data</h2>

If you need to write data back to the response, eg cookies or creating a redirect, an instance of `ResponseFormatter` is available in the controller:

```php
class LoginController extends Controller
{
    // ...

    public function logIn(LoginRequest $loginRequest): IHttpResponseMessage
    {
        $authResults = null;
        
        // Assume this logic resides in your application
        if (!$this->authenticator->tryLogin($loginRequest->username, $loginRequest->password, $authResults)) {
            return $this->unauthorized();
        }
        
        // Write a cookie containing the auth token back to the response
        $response = new Response(200);
        $authTokenCookie = new Cookie('authtoken', $authResults->getAuthToken(), time() + 3600);
        $this->responseFormatter->setCookie($response, $authTokenCookie);
        
        return $response;
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

Closures support the same [parameter resolution](#parameter-resolution) features as controller methods.  Here's the cool part - Aphiria will bind an instance of `Controller` to your closure, which means you can use [all the methods](#controllers), [request parsers](#parsing-request-data), and [response formatters](#formatting-response-data) available inside of `Controller` via `$this`.

<h2 id="controller-dependencies">Controller Dependencies</h2>

The API library provides support for auto-wiring your controllers.  In other words, it can scan your controllers' constructors for dependencies, resolve them, and then instantiate your controllers with those dependencies.  Dependency resolvers simply need to implement `IDependencyResolver`.  To make it easy for users of Opulence's DI container, you can use `ContainerDependencyResolver`.

```php
use Aphiria\Api\ContainerDependencyResolver;
use Opulence\Ioc\Container;

$container = new Container();
$dependencyResolver = new ContainerDependencyResolver($container);
```

Once you've instantiated your dependency resolver, pass it into your [request handler](#request-handlers) for auto-wiring.

<h1 id="middleware">Middleware</h1>

HTTP middleware are classes that are executed in a series of pipeline stages.  They manipulate the request and response to do things like authenticate users or enforce CSRF protection for certain routes.

The API library uses Aphiria's middleware library.  Learn more about it by <a href="https://github.com/aphiria/middleware#introduction" target="_blank">reading its documentation</a>.

<h2 id="configuring-middleware">Configuring Middleware</h2>

Aphiria uses dependency injection for type-hinted objects in middleware constructors.  So, if you need any objects in your `handle()` method, just specify them in the constructor.  Let's take a look at an example:

```php
namespace App\Application\Http\Middleware;

use Aphiria\Middleware\IMiddleware;
use Aphiria\Net\Http\Handlers\IRequestHandler;
use Aphiria\Net\Http\{IHttpRequestMessage, IHttpResponseMessage, Response};
use App\Domain\Authentication\Authenticator;

class Authentication implements IMiddleware
{
    private $authenticator;

    // Inject any dependencies your middleware needs
    public function __construct(IAuthenticator $authenticator)
    {
        $this->authenticator = $authenticator;
    }

    public function handle(IHttpRequestMessage $request, IRequestHandler $next): IHttpResponseMessage
    {
        if (!$this->authenticator->isLoggedIn($request)) {
            return new Response(401);
        }

        return $next->handle($request);
    }
}
```

You can then bind the middleware to your route:

```php
$routes->map('POST', 'articles')
    ->toMethod(ArticleController::class, 'createArticle')
    ->withMiddleware(Authentication::class);
```

Now, the `Authenticate` middleware will be run before the `createArticle()` controller method is called.  If the user is not logged in, s/he'll be given an unauthorized (401) response.

> **Note:** If middleware does not specifically call the `$next` request handler, none of the middleware after it in the pipeline will be run.

<h1 id="api-kernel">API Kernel</h1>

`ApiKernel` is usually the top layer of your application logic.  It is capable of matching a route and sending the request and response through [middleware](#middleware) to the [controller](#controllers).

Configuring your API is easy - you just need to set up a few things:

* <a href="https://github.com/aphiria/router#basic-usage" target="_blank">Routes</a>
* <a href="https://github.com/aphiria/net#content-negotiation" target="_blank">Content negotiator</a>
* [Dependency resolver](#controller-dependencies)

Handling a request from beginning to end is simple:

```php
use Aphiria\Api\ApiKernel;
use Aphiria\Net\Http\{RequestFactory, ResponseWriter};

// Assume your route matcher, dependency resolver, and content negotiator are already set
$request = (new RequestFactory)->createRequestFromSuperglobals($_SERVER);
$apiKernel = new ApiKernel(
    $routeMatcher,
    $dependencyResolver,
    $contentNegotiator
);
$response = $apiKernel->handle($request);
(new ResponseWriter)->writeResponse($response);
```

<h1 id="exception-handling">Exception Handling</h1>

Sometimes, your application is going to throw an unhandled exception or shut down unexpectedly.  When this happens, instead of showing an ugly PHP error, you can convert it to a nicely-formatted response.  To get set up, you can simply instantiate `ExceptionHandler` and register it with PHP:

```php
use Aphiria\Api\Exceptions\{ExceptionHandler, ExceptionResponseFactory};
use Aphiria\Net\Http\ContentNegotiation\NegotiatedResponseFactory;

// Assume the content negotiator was already set up
$exceptionResponseFactory = new ExceptionResponseFactory(
    new NegotiatedResponseFactory($contentNegotiator)
);

$exceptionHandler = new ExceptionHandler($exceptionResponseFactory);
$exceptionHandler->registerWithPhp();
```

By default, `ExceptionHandler` will convert any exception to a 500 response and use <a href="https://github.com/aphiria/net#content-negotiation" target="_blank">content negotiation</a> to determine the best format for the response body.  However, you can [customize your exception responses](#customizing-exception-responses).

<h2 id="customizing-exception-responses">Customizing Exception Responses</h2>

You might find yourself wanting to map a particular exception to a certain response.  In this case, you can use an exception response factory.  They are closures that take in the exception and the request, and return a response.

As an example, let's say that you want to return a 404 response when an `EntityNotFound` exception is thrown:

```php
use Aphiria\Api\Exceptions\{ExceptionResponseFactory, ExceptionResponseFactoryRegistry};
use Aphiria\Net\Http\Response;

// Register your custom exception response factories
$exceptionResponseFactories = new ExceptionResponseFactoryRegistry();
$exceptionResponseFactories->registerFactory(
    EntityNotFound::class,
    function (EntityNotFound $ex, ?IHttpRequestMessage $request) {
        return new Response(HttpStatusCodes::HTTP_NOT_FOUND);
    }
);

// Assume the content negotiator was already set up
$exceptionResponseFactory = new ExceptionResponseFactory(
    new NegotiatedResponseFactory($contentNegotiator),
    $exceptionResponseFactories
);

// Add it to the exception handler
$exceptionHandler = new ExceptionHandler($exceptionResponseFactory);
$exceptionHandler->registerWithPhp();
```

That's it.  Now, whenever an unhandled `EntityNotFound` exception is thrown, your application will return a 404 response.  You can also register multiple exception factories at once.  Just pass in an array, keyed by exception type:

```php
$exceptionResponseFactories->registerFactories([
    EntityNotFound::class => function (EntityNotFound $ex, ?IHttpRequestMessage $request) {
        return new Response(404);
    },
    // ...
]);
```

If you want to take advantage of automatic content negotiation, you can use a `NegotiatedResponseFactory` in your factory:

```php
use Aphiria\Net\Http\ContentNegotiation\NegotiatedResponseFactory;

// Assume the content negotiator was already set up
$negotiatedResponseFactory = new NegotiatedResponseFactory($contentNegotiator);
// ...
$exceptionResponseFactories->registerFactory(
    EntityNotFound::class,
    function (EntityNotFound $ex, ?IHttpRequestMessage $request) use ($negotiatedResponseFactory) {
        $error = new MyErrorObject('Entity not found');
    
        return $negotiatedResponseFactory->createResponse($request, 404, null, $error);
    }
);
```

If an unhandled `EntityNotFound` exception was thrown, your exception factory will use content negotiation to serialize `MyErrorObject` in the response body.

<h3 id="using-classes-to-create-exception-responses">Using Classes to Create Exception Responses</h3>

Sometimes, the logic inside your exception response factory might get a little too complicated to be easily readable in a `Closure`.  In this case, you can also use a POPO to encapsulate your response creation logic:

```php
class WhoopsResponseFactory
{
    public function createResponse(Exception $ex, ?IHttpRequestMessage $request): IHttpResponseMessage
    {
        $response = new Response();
        // Finish creating your response...
        
        return $response;
    }
}

$exceptionResponseFactories->registerFactory(
    Exception::class,
    function (Exception $ex, ?IHttpRequestMessage $request) {
        return (new WhoopsResponseFactory)->createResponse($ex, $request);
    }
);
```

<h2 id="logging">Logging</h2>

Unless you specify otherwise, a <a href="https://github.com/Seldaek/monolog" target="_blank">Monolog</a> logger to log all exceptions to the PHP error log.  However, you can override this with any PSR-3 logger:

```php
use Aphiria\Api\Exceptions\ExceptionResponseFactory;
use Aphiria\Net\Http\ContentNegotiation\ContentNegotiator;
use Aphiria\Net\Http\ContentNegotiation\MediaTypeFormatters\JsonMediaTypeFormatter;
use Aphiria\Net\Http\ContentNegotiation\NegotiatedResponseFactory;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;

// First, set up the factory that will create exception responses
$exceptionResponseFactory = new ExceptionResponseFactory(
    new NegotiatedResponseFactory(
        new ContentNegotiator([
            new JsonMediaTypeFormatter()
        ])
    )
);

// Next, set up our logger
$logger = new Logger('app');
$logger->pushHandler(new SyslogHandler());

// Now, set up our exception handler
$exceptionHandler = new ExceptionHandler($exceptionResponseFactory, $logger);
$exceptionHandler->registerWithPhp();
```

It's possible to specify some rules around the <a href="https://www.php-fig.org/psr/psr-3/#5-psrlogloglevel" target="_blank">PSR-3 log level</a> that an exception returns.  This could be useful for things like logging 500s as critical, but everything else as warnings.  Let's look at an example:

```php
$exceptionHandler = new ExceptionHandler(
    $exceptionResponseFactory,
    null, 
    [
        // Map exception types to their PSR-3 log levels
        HttpException::class => function (HttpException $ex) {
            if ($ex->getResponse()->getStatusCode() >= 500) {
                return LogLevel::CRITICAL;
            }
            
            return LogLevel::WARNING;
        }
    ]
);
$exceptionHandler->registerWithPhp();
```

Passing in an array of PSR-3 log levels will cause only those levels to be logged:

```php
$exceptionHandler = new ExceptionHandler(
    $exceptionResponseFactory,
    null,
    null,
    [LogLevel::CRITICAL, LogLevel::EMERGENCY]
);
```

By default, `LogLevel::ERROR`, `LogLevel::CRITICAL`, `LogLevel::ALERT`, and `LogLevel::EMERGENCY` will be logged if `null` is specified.

You can also control the level of PHP errors that are logged by specifying a bitwise value similar to what's in your _php.ini_:

```php
$exceptionHandler = new ExceptionHandler(
    $exceptionResponseFactory, 
    null, 
    null,
    null,
    E_ALL & ~E_NOTICE
);
$exceptionHandler->registerWithPhp();
```