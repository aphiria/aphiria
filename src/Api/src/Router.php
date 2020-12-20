<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api;

use Aphiria\Api\Controllers\Controller;
use Aphiria\Api\Controllers\ControllerRequestHandler;
use Aphiria\Api\Controllers\IRouteActionInvoker;
use Aphiria\Api\Controllers\RouteActionInvoker;
use Aphiria\ContentNegotiation\ContentNegotiator;
use Aphiria\ContentNegotiation\IContentNegotiator;
use Aphiria\DependencyInjection\IServiceResolver;
use Aphiria\DependencyInjection\ResolutionException;
use Aphiria\Middleware\ParameterizedMiddleware;
use Aphiria\Middleware\IMiddleware;
use Aphiria\Middleware\MiddlewarePipelineFactory;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\HttpStatusCodes;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Http\Response;
use Aphiria\Routing\Matchers\IRouteMatcher;
use Aphiria\Routing\Matchers\RouteMatchingResult;
use Aphiria\Routing\Middleware\MiddlewareBinding;
use Aphiria\Routing\RouteAction;
use InvalidArgumentException;

/**
 * Defines the request handler that performs routing
 */
class Router implements IRequestHandler
{
    /** @var IContentNegotiator The content negotiator */
    private IContentNegotiator $contentNegotiator;
    /** @var IRouteActionInvoker The route action invoker */
    private IRouteActionInvoker $routeActionInvoker;

    /**
     * @param IRouteMatcher $routeMatcher The route matcher
     * @param IServiceResolver $serviceResolver The service resolver
     * @param IContentNegotiator|null $contentNegotiator The content negotiator, or null if using the default negotiator
     * @param IRouteActionInvoker|null $routeActionInvoker The route action invoker
     */
    public function __construct(
        private IRouteMatcher $routeMatcher,
        private IServiceResolver $serviceResolver,
        IContentNegotiator $contentNegotiator = null,
        IRouteActionInvoker $routeActionInvoker = null
    ) {
        $this->contentNegotiator = $contentNegotiator ?? new ContentNegotiator();
        $this->routeActionInvoker = $routeActionInvoker ?? new RouteActionInvoker($this->contentNegotiator);
    }

    /**
     * @inheritdoc
     *
     * @psalm-suppress PossiblyNullPropertyFetch The matching result properties will be set because a non-match throws an exception
     * @psalm-suppress PossiblyNullArgument Ditto
     */
    public function handle(IRequest $request): IResponse
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
        $middlewarePipeline = (new MiddlewarePipelineFactory())->createPipeline(
            $this->createMiddlewareFromBindings($matchingResult->route->middlewareBindings),
            $controllerRequestHandler
        );

        return $middlewarePipeline->handle($request);
    }

    /**
     * Creates a controller from a route action
     *
     * @param RouteAction $routeAction The route action to create the controller from
     * @param Controller|null $controller The "out" parameter that will contain the controller
     * @param callable|null $routeActionDelegate The "out" parameter that will contain the route action delegate
     * @throws ResolutionException Thrown if the controller could not be resolved
     */
    private function createController(
        RouteAction $routeAction,
        ?Controller &$controller,
        ?callable &$routeActionDelegate
    ): void {
        $controller = $this->serviceResolver->resolve($routeAction->className);
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

        if (!$controller instanceof Controller) {
            throw new InvalidArgumentException(
                sprintf('Controller %s does not extend %s', $controller::class, Controller::class)
            );
        }
    }

    /**
     * Creates middleware instances from middleware bindings
     *
     * @param MiddlewareBinding[] $middlewareBindings The list of middleware bindings to create instances from
     * @return IMiddleware[] The middleware instances
     * @throws ResolutionException Thrown if the middleware could not be resolved
     */
    private function createMiddlewareFromBindings(array $middlewareBindings): array
    {
        $middlewareList = [];

        foreach ($middlewareBindings as $middlewareBinding) {
            $middleware = $this->serviceResolver->resolve($middlewareBinding->className);

            if (!$middleware instanceof IMiddleware) {
                throw new InvalidArgumentException(
                    sprintf('Middleware %s does not implement %s', $middleware::class, IMiddleware::class)
                );
            }

            if ($middleware instanceof ParameterizedMiddleware) {
                $middleware->setParameters($middlewareBinding->parameters);
            }

            $middlewareList[] = $middleware;
        }

        return $middlewareList;
    }

    /**
     * Gets the matching route for the input request
     *
     * @param IRequest $request The current request
     * @return RouteMatchingResult The route matching result
     * @throws HttpException Thrown if there was no matching route, or if the request was invalid for the matched route
     */
    private function matchRoute(IRequest $request): RouteMatchingResult
    {
        $uri = $request->getUri();
        $matchingResult = $this->routeMatcher->matchRoute($request->getMethod(), $uri->getHost() ?? '', $uri->getPath() ?? '');

        if (!$matchingResult->matchFound) {
            if ($matchingResult->methodIsAllowed === null) {
                throw new HttpException(HttpStatusCodes::NOT_FOUND, "No route found for {$request->getUri()}");
            }

            $response = new Response(HttpStatusCodes::METHOD_NOT_ALLOWED);
            $response->getHeaders()->add('Allow', $matchingResult->allowedMethods);

            throw new HttpException($response, 'Method not allowed');
        }

        return $matchingResult;
    }
}
