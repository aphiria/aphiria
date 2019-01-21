<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api;

use Closure;
use InvalidArgumentException;
use Opulence\Api\Controllers\{Controller, ControllerRequestHandler, IRouteActionInvoker, RouteActionInvoker};
use Opulence\Middleware\AttributeMiddleware;
use Opulence\Middleware\IMiddleware;
use Opulence\Middleware\MiddlewarePipelineFactory;
use Opulence\Net\Http\ContentNegotiation\IContentNegotiator;
use Opulence\Net\Http\Handlers\IRequestHandler;
use Opulence\Net\Http\{HttpException, HttpStatusCodes, IHttpRequestMessage, IHttpResponseMessage, Response};
use Opulence\Routing\Matchers\{IRouteMatcher, RouteMatchingResult};
use Opulence\Routing\Middleware\MiddlewareBinding;
use Opulence\Routing\RouteAction;

/**
 * Defines the API kernel
 */
class ApiKernel implements IRequestHandler
{
    /** @var IRouteMatcher The route matcher */
    private $routeMatcher;
    /** @var IDependencyResolver The dependency resolver */
    private $dependencyResolver;
    /** @var IContentNegotiator The content negotiator */
    private $contentNegotiator;
    /** @var MiddlewarePipelineFactory The middleware pipeline factory */
    private $middlewarePipelineFactory;
    /** @var IRouteActionInvoker The route action invoker */
    private $routeActionInvoker;

    /**
     * @param IRouteMatcher $routeMatcher The route matcher
     * @param IDependencyResolver $dependencyResolver The dependency resolver
     * @param IContentNegotiator $contentNegotiator The content negotiator
     * @param MiddlewarePipelineFactory|null $middlewarePipelineFactory THe middleware pipeline factory
     * @param IRouteActionInvoker|null $routeActionInvoker The route action invoker
     */
    public function __construct(
        IRouteMatcher $routeMatcher,
        IDependencyResolver $dependencyResolver,
        IContentNegotiator $contentNegotiator,
        MiddlewarePipelineFactory $middlewarePipelineFactory = null,
        IRouteActionInvoker $routeActionInvoker = null
    ) {
        $this->routeMatcher = $routeMatcher;
        $this->dependencyResolver = $dependencyResolver;
        $this->contentNegotiator = $contentNegotiator;
        $this->middlewarePipelineFactory = $middlewarePipelineFactory ?? new MiddlewarePipelineFactory();
        $this->routeActionInvoker = $routeActionInvoker ?? new RouteActionInvoker($this->contentNegotiator);
    }

    /**
     * @inheritdoc
     */
    public function handle(IHttpRequestMessage $request): IHttpResponseMessage
    {
        $matchingResult = $this->matchRoute($request);
        $controller = $routeActionDelegate = null;
        $this->createController($matchingResult->route->action, $controller, $routeActionDelegate);
        $controllerRequestHandler = new ControllerRequestHandler(
            $controller,
            $routeActionDelegate,
            $matchingResult->routeVariables,
            $this->contentNegotiator,
            $this->routeActionInvoker
        );
        $middlewarePipeline = $this->middlewarePipelineFactory->createPipeline(
            $this->createMiddlewareFromBindings($matchingResult->route->middlewareBindings),
            $controllerRequestHandler
        );

        return $middlewarePipeline->handle($request);
    }

    /**
     * Creates a controller from a route action
     *
     * @param RouteAction $routeAction The route action to create the controller from
     * @param Controller $controller The "out" parameter that will contain the controller
     * @param callable $routeActionDelegate The "out" parameter that will contain the route action delegate
     * @throws DependencyResolutionException Thrown if the controller could not be resolved
     */
    private function createController(
        RouteAction $routeAction,
        ?Controller &$controller,
        ?callable &$routeActionDelegate
    ): void {
        if ($routeAction->usesMethod()) {
            $controller = $this->dependencyResolver->resolve($routeAction->className);
            $routeActionDelegate = [$controller, $routeAction->methodName];

            if (!\is_callable($routeActionDelegate)) {
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
            $routeActionDelegate = Closure::bind($routeAction->closure, $controller, Controller::class);
        }

        if (!$controller instanceof Controller) {
            throw new InvalidArgumentException(
                sprintf('Controller %s does not extend %s', \get_class($controller), Controller::class)
            );
        }
    }

    /**
     * Creates middleware instances from middleware bindings
     *
     * @param MiddlewareBinding[] $middlewareBindings The list of middleware bindings to create instances from
     * @return IMiddleware[] The middleware instances
     * @throws DependencyResolutionException Thrown if the middleware could not be resolved
     */
    private function createMiddlewareFromBindings(array $middlewareBindings): array {
        $middlewareList = [];

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

            $middlewareList[] = $middleware;
        }

        return $middlewareList;
    }

    /**
     * Gets the matching route for the input request
     *
     * @param IHttpRequestMessage $request The current request
     * @return RouteMatchingResult The route matching result
     * @throws HttpException Thrown if there was no matching route, or if the request was invalid for the matched route
     */
    private function matchRoute(IHttpRequestMessage $request): RouteMatchingResult
    {
        $uri = $request->getUri();
        $matchingResult = $this->routeMatcher->matchRoute($request->getMethod(), $uri->getHost(), $uri->getPath());

        if (!$matchingResult->matchFound) {
            if ($matchingResult->methodIsAllowed === null) {
                throw new HttpException(HttpStatusCodes::HTTP_NOT_FOUND, "No route found for {$request->getUri()}");
            }

            $response = new Response(HttpStatusCodes::HTTP_METHOD_NOT_ALLOWED);
            $response->getHeaders()->add('Allow', $matchingResult->allowedMethods);

            throw new HttpException($response, 'Method not allowed');
        }

        return $matchingResult;
    }
}