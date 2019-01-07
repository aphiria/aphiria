<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Handlers;

use Closure;
use InvalidArgumentException;
use Opulence\Api\Controller;
use Opulence\Api\Middleware\AttributeMiddleware;
use Opulence\Api\Middleware\IMiddleware;
use Opulence\Net\Http\ContentNegotiation\IContentNegotiator;
use Opulence\Net\Http\ContentNegotiation\NegotiatedResponseFactory;
use Opulence\Net\Http\Formatting\RequestParser;
use Opulence\Net\Http\Handlers\IRequestHandler;
use Opulence\Net\Http\HttpException;
use Opulence\Net\Http\HttpStatusCodes;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;
use Opulence\Net\Http\Response;
use Opulence\Pipelines\Pipeline;
use Opulence\Routing\Matchers\IRouteMatcher;
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

    /**
     * @param IRouteMatcher $routeMatcher The route matcher
     * @param IDependencyResolver $dependencyResolver The dependency resolver
     * @param IContentNegotiator $contentNegotiator The content negotiator
     * @param IRouteActionInvoker|null $routeActionInvoker The route action invoker
     */
    public function __construct(
        IRouteMatcher $routeMatcher,
        IDependencyResolver $dependencyResolver,
        IContentNegotiator $contentNegotiator,
        IRouteActionInvoker $routeActionInvoker = null
    ) {
        $this->routeMatcher = $routeMatcher;
        $this->dependencyResolver = $dependencyResolver;
        $this->contentNegotiator = $contentNegotiator;
        $this->routeActionInvoker = $routeActionInvoker ?? new RouteActionInvoker($this->contentNegotiator);
    }

    /**
     * @inheritdoc
     */
    public function handle(IHttpRequestMessage $request): IHttpResponseMessage
    {
        $uri = $request->getUri();
        $matchingResult = $this->routeMatcher->matchRoute($request->getMethod(), $uri->getHost(), $uri->getPath());

        if (!$matchingResult->matchFound) {
            if ($matchingResult->methodIsAllowed) {
                throw new HttpException(HttpStatusCodes::HTTP_NOT_FOUND, "No route found for {$request->getUri()}");
            }

            $response = new Response(HttpStatusCodes::HTTP_METHOD_NOT_ALLOWED);
            $response->getHeaders()->add('Allow', $matchingResult->allowedMethods);

            throw new HttpException($response, 'Method not allowed');
        }

        $routeAction = $matchingResult->route->action;

        if ($routeAction->usesMethod()) {
            $controller = $this->dependencyResolver->resolve($routeAction->className);
            $controllerCallable = [$controller, $routeAction->methodName];

            if (!\is_callable($controllerCallable)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Controller method %s::%s() does not exist',
                        $routeAction->className,
                        $routeAction->methodName
                    )
                );
            }
        } else {
            $controller = new Controller();
            $controllerCallable = Closure::bind($routeAction->closure, $controller, Controller::class);
        }

        if (!$controller instanceof Controller) {
            throw new InvalidArgumentException(
                sprintf('Controller %s does not extend %s', \get_class($controller), Controller::class)
            );
        }

        $controller->setRequest($request);
        $controller->setRequestParser(new RequestParser);
        $controller->setContentNegotiator($this->contentNegotiator);
        $controller->setNegotiatedResponseFactory(new NegotiatedResponseFactory($this->contentNegotiator));
        $middleware = $this->resolveMiddleware($matchingResult->route->middlewareBindings);

        return (new Pipeline)->send($request)
            ->through($middleware, 'handle')
            ->then(function () use ($controllerCallable, $request, $matchingResult) {
                return $this->routeActionInvoker->invokeRouteAction(
                    $controllerCallable,
                    $request,
                    $matchingResult
                );
            })
            ->execute();
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
            $middleware = $this->dependencyResolver->resolve($middlewareBinding->className);

            if (!$middleware instanceof IMiddleware) {
                throw new InvalidArgumentException(
                    sprintf('Middleware %s does not implement %s', \get_class($middleware), IMiddleware::class)
                );
            }

            if ($middleware instanceof AttributeMiddleware) {
                $middleware->setAttributes($middlewareBinding->attributes);
            }

            $resolvedMiddleware[] = $middleware;
        }

        return $resolvedMiddleware;
    }
}
