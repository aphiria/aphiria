<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api;

use Aphiria\Api\Controllers\Controller;
use Aphiria\Api\Controllers\ControllerRequestHandler;
use Aphiria\Api\Controllers\IRouteActionInvoker;
use Aphiria\Api\Controllers\RouteActionInvoker;
use Aphiria\Authentication\IUserAccessor;
use Aphiria\Authentication\RequestPropertyUserAccessor;
use Aphiria\ContentNegotiation\ContentNegotiator;
use Aphiria\ContentNegotiation\IContentNegotiator;
use Aphiria\DependencyInjection\IServiceResolver;
use Aphiria\DependencyInjection\ResolutionException;
use Aphiria\Middleware\IMiddleware;
use Aphiria\Middleware\MiddlewarePipelineFactory;
use Aphiria\Middleware\ParameterizedMiddleware;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\HttpStatusCode;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Http\Response;
use Aphiria\Routing\Matchers\IRouteMatcher;
use Aphiria\Routing\Matchers\RouteMatchingResult;
use Aphiria\Routing\Middleware\MiddlewareBinding;
use Aphiria\Routing\RouteAction;
use Closure;
use InvalidArgumentException;

/**
 * Defines the request handler that performs routing
 */
class Router implements IRequestHandler
{
    /** @var IRouteActionInvoker The route action invoker */
    private readonly IRouteActionInvoker $routeActionInvoker;

    /**
     * @param IRouteMatcher $routeMatcher The route matcher
     * @param IServiceResolver $serviceResolver The service resolver
     * @param IContentNegotiator $contentNegotiator The content negotiator
     * @param IRouteActionInvoker|null $routeActionInvoker The route action invoker
     */
    public function __construct(
        private readonly IRouteMatcher $routeMatcher,
        private readonly IServiceResolver $serviceResolver,
        private readonly IContentNegotiator $contentNegotiator = new ContentNegotiator(),
        ?IRouteActionInvoker $routeActionInvoker = null,
        private readonly IUserAccessor $userAccessor = new RequestPropertyUserAccessor()
    ) {
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
        /**
         * @var Controller $controller
         * @var Closure $routeActionDelegate
         */
        $controller = $routeActionDelegate = null;
        $this->createController($matchingResult->route->action, $controller, $routeActionDelegate);
        $controllerRequestHandler = new ControllerRequestHandler(
            $controller,
            $routeActionDelegate,
            $matchingResult->routeVariables,
            $this->contentNegotiator,
            $this->routeActionInvoker,
            $this->userAccessor
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
     * @param Closure|null $routeActionDelegate The "out" parameter that will contain the route action delegate
     * @throws ResolutionException Thrown if the controller could not be resolved
     */
    private function createController(
        RouteAction $routeAction,
        ?Controller &$controller,
        ?Closure &$routeActionDelegate
    ): void {
        $controller = $this->serviceResolver->resolve($routeAction->className);
        $routeActionDelegate = Closure::fromCallable([$controller, $routeAction->methodName]);

        if (!$controller instanceof Controller) {
            throw new InvalidArgumentException(
                \sprintf('Controller %s does not extend %s', $controller::class, Controller::class)
            );
        }
    }

    /**
     * Creates middleware instances from middleware bindings
     *
     * @param list<MiddlewareBinding> $middlewareBindings The list of middleware bindings to create instances from
     * @return list<IMiddleware> The middleware instances
     * @throws ResolutionException Thrown if the middleware could not be resolved
     */
    private function createMiddlewareFromBindings(array $middlewareBindings): array
    {
        $middlewareList = [];

        foreach ($middlewareBindings as $middlewareBinding) {
            $middleware = $this->serviceResolver->resolve($middlewareBinding->className);

            if (!$middleware instanceof IMiddleware) {
                throw new InvalidArgumentException(
                    \sprintf('Middleware %s does not implement %s', $middleware::class, IMiddleware::class)
                );
            }

            if ($middleware instanceof ParameterizedMiddleware) {
                $middleware->parameters = $middlewareBinding->parameters;
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
        $matchingResult = $this->routeMatcher->matchRoute($request->method, $request->uri->host ?? '', $request->uri->path ?? '');

        if (!$matchingResult->matchFound) {
            if ($matchingResult->methodIsAllowed === null) {
                throw new HttpException(HttpStatusCode::NotFound, "No route found for {$request->uri}");
            }

            $response = new Response(HttpStatusCode::MethodNotAllowed);
            $response->headers->add('Allow', $matchingResult->allowedMethods);

            throw new HttpException($response, 'Method not allowed');
        }

        return $matchingResult;
    }
}
