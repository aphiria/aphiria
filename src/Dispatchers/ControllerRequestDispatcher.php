<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Dispatchers;

use InvalidArgumentException;
use Opulence\Api\Controller;
use Opulence\Api\ControllerContext;
use Opulence\Api\Middleware\AttributeMiddleware;
use Opulence\Api\Middleware\IMiddleware;
use Opulence\Net\Http\ContentNegotiation\IContentNegotiator;
use Opulence\Net\Http\Dispatchers\IRequestDispatcher;
use Opulence\Net\Http\HttpException;
use Opulence\Net\Http\HttpStatusCodes;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;
use Opulence\Pipelines\Pipeline;
use Opulence\Routing\Matchers\IRouteMatcher;
use Opulence\Routing\Matchers\RouteNotFoundException;
use Opulence\Routing\Middleware\MiddlewareBinding;

/**
 * Defines the controller request dispatcher
 */
class ControllerRequestDispatcher implements IRequestDispatcher
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
            $controller = $this->dependencyResolver->resolve($matchedRoute->getAction()->getClassName());

            if (!$controller instanceof Controller) {
                throw new InvalidArgumentException(
                    sprintf('Controller %s does not extend %s', \get_class($controller), Controller::class)
                );
            }

            $controllerContext = new ControllerContext(
                $controller,
                $request,
                $this->contentNegotiator->negotiateRequestContent($request),
                $this->contentNegotiator->negotiateResponseContent($request),
                $matchedRoute
            );
            $controller->setControllerContext($controllerContext);
            // Todo: This doesn't handle global middleware at all
            $middleware = $this->resolveMiddleware($matchedRoute->getMiddlewareBindings());

            return (new Pipeline)->send($request)
                ->through($middleware, 'handle')
                ->then(function () use ($controllerContext) {
                    return $this->routeActionInvoker->invokeRouteAction($controllerContext);
                })
                ->execute();
        } catch (RouteNotFoundException $ex) {
            throw new HttpException(
                HttpStatusCodes::HTTP_NOT_FOUND,
                "No route found for {$request->getUri()}",
                0,
                $ex
            );
        } catch (DependencyResolutionException $ex) {
            throw new HttpException(
                HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR,
                'Could not resolve controller',
                0,
                $ex
            );
        } catch (Exception | Throwable $ex) {
            // Don't re-throw it as an HttpException
            if ($ex instanceof HttpException) {
                throw $ex;
            }

            throw new HttpException(
                HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR,
                'Failed dispatch request',
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
