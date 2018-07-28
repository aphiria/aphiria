<?php /** @noinspection PhpInternalEntityUsedInspection */

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Handlers;

use Closure;
use InvalidArgumentException;
use Opulence\Api\Controller;
use Opulence\Api\Exceptions\ExceptionHandler;
use Opulence\Api\Exceptions\IExceptionHandler;
use Opulence\Api\Middleware\AttributeMiddleware;
use Opulence\Api\Middleware\IMiddleware;
use Opulence\Api\RequestContext;
use Opulence\Net\Http\ContentNegotiation\IContentNegotiator;
use Opulence\Net\Http\Handlers\IRequestHandler;
use Opulence\Net\Http\HttpException;
use Opulence\Net\Http\HttpStatusCodes;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;
use Opulence\Pipelines\Pipeline;
use Opulence\Routing\Matchers\IRouteMatcher;
use Opulence\Routing\Matchers\RouteNotFoundException;
use Opulence\Routing\Middleware\MiddlewareBinding;

/**
 * Defines the controller request handler
 */
class ControllerRequestHandler implements IRequestHandler
{
    /** @var IRouteMatcher The route matcher */
    private $routeMatcher;
    /** @var IDependencyResolver The dependency resolver */
    private $dependencyResolver;
    /** @var IContentNegotiator The content negotiator */
    private $contentNegotiator;
    /** @var IRouteActionInvoker The route action invoker */
    private $routeActionInvoker;
    /** @var IExceptionHandler The exception handler to use */
    private $exceptionHandler;

    /**
     * @param IRouteMatcher $routeMatcher The route matcher
     * @param IDependencyResolver $dependencyResolver The dependency resolver
     * @param IContentNegotiator $contentNegotiator The content negotiator
     * @param IRouteActionInvoker|null $routeActionInvoker The route action invoker
     * @param IExceptionHandler|null $exceptionHandler The exception handler to use
     */
    public function __construct(
        IRouteMatcher $routeMatcher,
        IDependencyResolver $dependencyResolver,
        IContentNegotiator $contentNegotiator,
        IRouteActionInvoker $routeActionInvoker = null,
        IExceptionHandler $exceptionHandler = null
    ) {
        $this->routeMatcher = $routeMatcher;
        $this->dependencyResolver = $dependencyResolver;
        $this->contentNegotiator = $contentNegotiator;
        $this->routeActionInvoker = $routeActionInvoker ?? new RouteActionInvoker();
        $this->exceptionHandler = $exceptionHandler ?? new ExceptionHandler();
    }

    /**
     * @inheritdoc
     */
    public function handle(IHttpRequestMessage $request): IHttpResponseMessage
    {
        try {
            $uri = $request->getUri();
            $matchedRoute = $this->routeMatcher->match($request->getMethod(), $uri->getHost(), $uri->getPath());
            $routeAction = $matchedRoute->getAction();
            $requestContext = new RequestContext(
                $request,
                $this->contentNegotiator->negotiateRequestContent($request),
                $this->contentNegotiator->negotiateResponseContent($request),
                $matchedRoute
            );
            $this->exceptionHandler->setRequestContext($requestContext);

            if ($routeAction->usesMethod()) {
                $controller = $this->dependencyResolver->resolve($routeAction->getClassName());
                $controllerCallable = [$controller, $routeAction->getMethodName()];
            } else {
                $controller = new Controller();
                $controllerCallable = Closure::bind($routeAction->getClosure(), $controller, Controller::class);
            }

            if (!$controller instanceof Controller) {
                throw new InvalidArgumentException(
                    sprintf('Controller %s does not extend %s', \get_class($controller), Controller::class)
                );
            }

            $controller->setRequestContext($requestContext);
            $middleware = $this->resolveMiddleware($matchedRoute->getMiddlewareBindings());

            return (new Pipeline)->send($request)
                ->through($middleware, 'handle')
                ->then(function () use ($controllerCallable, $requestContext) {
                    return $this->routeActionInvoker->invokeRouteAction($controllerCallable, $requestContext);
                })
                ->execute();
        } catch (RouteNotFoundException $ex) {
            throw new HttpException(
                HttpStatusCodes::HTTP_NOT_FOUND,
                "No route found for {$request->getUri()}",
                0,
                $ex
            );
        }
    }

    /**
     * Resolves middleware instances from bindings
     *
     * @param MiddlewareBinding[] $middlewareBindings The list of middleware bindings
     * @return IMiddleware[] The resolved middleware
     * @throws DependencyResolutionException Thrown if there was an error resolving the middleware
     * @throws InvalidArgumentException Thrown if any of the middleware did not implement the correct interface
     */
    private function resolveMiddleware(array $middlewareBindings): array
    {
        $resolvedMiddleware = [];

        foreach ($middlewareBindings as $middlewareBinding) {
            $middleware = $this->dependencyResolver->resolve($middlewareBinding->getClassName());

            if (!$middleware instanceof IMiddleware) {
                throw new InvalidArgumentException(
                    sprintf('Middleware %s does not implement %s', \get_class($middleware), IMiddleware::class)
                );
            }

            if ($middleware instanceof AttributeMiddleware) {
                $middleware->setAttributes($middlewareBinding->getAttributes());
            }

            $resolvedMiddleware[] = $middleware;
        }

        return $resolvedMiddleware;
    }
}
