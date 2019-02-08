<h1>Middleware</h1>

[![Build Status](https://travis-ci.com/aphiria/middleware.svg)](https://travis-ci.com/aphiria/middleware)
[![Latest Stable Version](https://poser.pugx.org/aphiria/middleware/v/stable.svg)](https://packagist.org/packages/aphiria/middleware)
[![Latest Unstable Version](https://poser.pugx.org/aphiria/middleware/v/unstable.svg)](https://packagist.org/packages/aphiria/middleware)
[![License](https://poser.pugx.org/aphiria/middleware/license.svg)](https://packagist.org/packages/aphiria/middleware)

> **Note:** This library is still in development.

<h1>Table of Contents</h1>

1. [Introduction](#introduction)
    1. [Installation](#installation)
2. [Basic Usage](#basic-usage)
    1. [Manipulating the Request](#manipulating-the-request)
    2. [Manipulating the Response](#manipulating-the-response)
    3. [Middleware Attributes](#middleware-attributes)
3. [Executing Middleware](#executing-middleware)

<h1 id="introduction">Introduction</h1>

The middleware library provides developers a way of defining route middleware for their applications.  Middleware are simply layers of request processing before and after a controller action is invoked.  This is extremely useful for actions like authorization, logging, and request/response decoration.

<h2 id="installation">Installation</h2>

You can install this library by including the following package name in your _composer.json_:

```bash
"aphiria/middleware": "1.0.*"
```

<h1 id="basic-usage">Basic Usage</h1>

Middleware have a simple signature:

```php
use Aphiria\Net\Http\Handlers\IRequestHandler;
use Aphiria\Net\Http\{IHttpRequestMessage, IHttpResponseMessage};

interface IMiddleware
{
    public function handle(IHttpRequestMessage $request, IRequestHandler $next): IHttpResponseMessage;
}
```

`IMiddleware::handle()` takes in the current request and

1. Optionally manipulates it
2. Passes the request on to the next request handler in the pipeline
3. Optionally manipulates the response returned by the next request handler
4. Returns the response

<h2 id="manipulating-the-request">Manipulating the Request</h2>

To manipulate the request before it gets to the controller, make changes to it before calling `$next($request)`:

```php
use Aphiria\Middleware\IMiddleware;
use Aphiria\Net\Http\Handlers\IRequestHandler;
use Aphiria\Net\Http\{IHttpRequestMessage, IHttpResponseMessage};

class RequestManipulator implements IMiddleware
{
    public function handle(IHttpRequestMessage $request, IRequestHandler $next): IHttpResponseMessage
    {
        // Do our work before returning $next->handle($request)
        $request->getProperties()->add('Foo', 'bar');

        return $next->handle($request);
    }
}
```

<h2 id="manipulating-the-response">Manipulating the Response</h2>

To manipulate the response after the controller has done its work, do the following:

```php
use Aphiria\Middleware\IMiddleware;
use Aphiria\Net\Http\Handlers\IRequestHandler;
use Aphiria\Net\Http\{IHttpRequestMessage, IHttpResponseMessage};

class ResponseManipulator implements IMiddleware
{
    public function handle(IHttpRequestMessage $request, IRequestHandler $next): IHttpResponseMessage
    {
        $response = $next->handle($request);

        // Make our changes
        $response->getHeaders()->add('Foo', 'bar');

        return $response;
    }
}
```

<h2 id="middleware-attributes">Middleware Attributes</h2>

Occasionally, you'll find yourself wanting to pass primitive values to middleware to indicate something such as a required role to execute an action.  In these cases, your middleware should extend `Aphiria\Middleware\AttributeMiddleware`:

```php
use Aphiria\Middleware\AttributeMiddleware;
use Aphiria\Net\Http\Handlers\IRequestHandler;
use Aphiria\Net\Http\{HttpException, IHttpRequestMessage, IHttpResponseMessage};

class RoleMiddleware extends AttributeMiddleware
{
    private $user;

    // Inject any dependencies your middleware needs
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle(IHttpRequestMessage $request, IRequestHandler $next): IHttpResponseMessage
    {
        // Attributes are available via $this->attributes
        if (!$this->user->hasRole($this->attributes['role'])) {
            throw new HttpException(403);
        }

        return $next->handle($request);
    }
}
```

To actually specify `role`, pass it into your route configuration:

```php
$routes->map('GET', 'foo')
    ->toMethod(MyController::class, 'myMethod')
    ->withMiddleware(RoleMiddleware::class, ['role' => 'admin']);
```

<h1 id="executing-middleware">Executing Middleware</h1>

Typically, middleware are wrapped in request handlers (eg `MiddlewareRequestHandler`) and executed in a pipeline (as in Aphiria's <a href="https://github.com/aphiria/api" target="_blank">API library</a>).  You can create this pipeline using `MiddlewarePipelineFactory`:

```php
use Aphiria\Middleware\MiddlewarePipelineFactory;
use Aphiria\Net\Http\RequestFactory;

// Assume these are defined by your application
$loggingMiddleware = new LoggingMiddleware();
$authMiddleware = new AuthenticationMiddleware();
$controllerHandler = new ControllerRequestHandler();

$pipeline = (new MiddlewarePipelineFactory)->createPipeline(
    [$loggingMiddleware, $authMiddleware],
    $controllerHandler
);
``` 

`$pipeline` will itself be a request handler, which you can then send a request through and receive a response:

```php
$request = (new RequestFactory)->createRequestFromSuperglobals($_SERVER);
$response = $pipeline->handle($request);
```