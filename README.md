<h1>Middleware</h1>

> **Note:** This library is still in development.

<h1>Table of Contents</h1>

1. [Introduction](#introduction)
    1. [Installation](#installation)
2. [Middleware](#middleware)
    1. [Manipulating the Request](#manipulating-the-request)
    2. [Manipulating the Response](#manipulating-the-response)
    3. [Middleware Attributes](#middleware-attributes)

<h1 id="introduction">Introduction</h1>

The middleware library provides developers a way of defining route middleware for their applications.  Middleware are simply layers of request processing before and after a controller action is invoked.  This is extremely useful for actions like authorization, logging, and request/response decoration.

<h2 id="installation">Installation</h2>

You can install this library by including the following package name in your _composer.json_:

```
"opulence/middleware": "1.0.*"
```

<h1 id="middleware">Middleware</h1>

HTTP middleware are classes that are executed in a series of pipeline stages.  They manipulate the request and response to do things like authenticate users or enforce CSRF protection for certain routes.  They are executed in series in a pipeline.

Opulence uses dependency injection for type-hinted objects in a `Middleware` constructor.  So, if you need any objects in your `handle()` method, just specify them in the constructor.  Let's take a look at an example:

```php
namespace App\Application\Http\Middleware;

use App\Domain\Authentication\Authenticator;
use Opulence\Middleware\IMiddleware;
use Opulence\Net\Http\Handlers\IRequestHandler;
use Opulence\Net\Http\{IHttpRequestMessage, IHttpResponseMessage, Response};

class Authentication implements IMiddleware
{
    private $authenticator = null;

    // Inject any dependencies your middleware needs
    public function __construct(Authenticator $authenticator)
    {
        $this->authenticator = $authenticator;
    }

    // $next is the next request handler in the pipeline
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
$routes->map('POST', 'posts')
    ->toMethod(PostController::class, 'createPost')
    ->withMiddleware(Authentication::class);
```

Now, the `Authenticate` middleware will be run before the `createPost()` controller method is called.  If the user is not logged in, s/he'll be given an unauthorized (401) response.

> **Note:** If middleware does not specifically call the `$next` request handler, none of the middleware after it in the pipeline will be run.

<h2 id="manipulating-the-request">Manipulating the Request</h2>

To manipulate the request before it gets to the controller, make changes to it before calling `$next($request)`:

```php
use Opulence\Middleware\IMiddleware;
use Opulence\Net\Http\Handlers\IRequestHandler;
use Opulence\Net\Http\{IHttpRequestMessage, IHttpResponseMessage};

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
use Opulence\Middleware\IMiddleware;
use Opulence\Net\Http\Handlers\IRequestHandler;
use Opulence\Net\Http\{IHttpRequestMessage, IHttpResponseMessage};

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

Occasionally, you'll find yourself wanting to pass primitive values to middleware to indicate something such as a required role to execute an action.  In these cases, your middleware should extend `Opulence\Middleware\AttributeMiddleware`:

```php
use Opulence\Middleware\AttributeMiddleware;
use Opulence\Net\Http\Handlers\IRequestHandler;
use Opulence\Net\Http\{HttpException, IHttpRequestMessage, IHttpResponseMessage};

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