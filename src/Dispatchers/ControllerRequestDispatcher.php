<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Dispatchers;

use Opulence\Api\ControllerContext;
use Opulence\Api\Middleware\AttributeMiddleware;
use Opulence\Api\Middleware\IMiddleware;
use Opulence\Net\Http\ContentNegotiation\IContentNegotiator;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;
use Opulence\Pipelines\Pipeline;
use Opulence\Pipelines\PipelineException;
use Opulence\Routing\Matchers\IRouteMatcher;
use Opulence\Routing\Matchers\Middleware\MiddlewareBinding;
use Opulence\Routing\Matchers\RouteNotFoundException;

/**
 * Defines the controller request dispatcher
 */
class ControllerRequestDispatcher implements IControllerRequestDispatcher
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
     * @param IContentNegotiator|null $contentNegotiator The content negotiator
     * @param IRouteActionInvoker|null $routeActionInvoker The route action invoker
     */
    public function __construct(
        IRouteMatcher $routeMatcher,
        IDependencyResolver $dependencyResolver,
        IContentNegotiator $contentNegotiator = null,
        IRouteActionInvoker $routeActionInvoker = null
    ) {
        $this->routeMatcher = $routeMatcher;
        $this->dependencyResolver = $dependencyResolver;
        $this->contentNegotiator = $contentNegotiator ?? new ContentNegotiator();
        $this->routeActionInvoker = $routeActionInvoker ?? new RouteActionInvoker();
    }

    /**
     * @inheritdoc
     */
    public function dispatchRequest(IHttpRequestMessage $request): IHttpResponseMessage
    {
        try {
            $uri = $request->getUri();
            $matchedRoute = $this->routeMatcher->match($request->getMethod(), $uri->getHost(), $uri->getPath());
            // Todo: Where do I pass in media type formatters and supported languages?
            $controllerContext = new ControllerContext(
                $this->dependencyResolver->resolve($matchedRoute->getAction()->getClassName()),
                $request,
                $this->contentNegotiator->negotiateRequestContent($request, []),
                $this->contentNegotiator->negotiateResponseContent($request, [], []),
                $matchedRoute
            );
            // Todo: This doesn't handle global middleware at all
            $middleware = $this->resolveMiddleware($matchedRoute->getMiddlewareBindings());

            return (new Pipeline)->send($request)
                ->through($middleware, 'handle')
                ->then(function () use ($controllerContext) {
                    return $this->routeActionInvoker->invokeRouteAction($controllerContext);
                })
                ->execute();
        } catch (RouteNotFoundException $ex) {
            // Todo: Throw this as a more descriptive domain exception
        } catch (DependencyResolutionException $ex) {
            // Todo: Throw this as a more descriptive domain exception
        } catch (PipelineException $ex) {
            // Todo: Throw this as a more descriptive domain exception
        }
    }

    /**
     * Resolves middleware instances from bindings
     *
     * @param MiddlewareBinding[] $middlewareBindings The list of middleware bindings
     * @return IMiddleware[] The resolved middleware
     * @throws DependencyResolutionException Thrown if there was an error resolving the middleware
     */
    private function resolveMiddleware(array $middlewareBindings): array
    {
        $resolvedMiddleware = [];

        foreach ($middlewareBindings as $middlewareBinding) {
            $middleware = $this->dependencyResolver->resolve($middlewareBinding->getClassName());

            if ($middleware instanceof AttributeMiddleware) {
                $middleware->setAttributes($middlewareBinding->getAttributes());
            }

            $resolvedMiddleware[] = $middleware;
        }

        return $resolvedMiddleware;
    }
}
