<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Middleware;

use InvalidArgumentException;
use Opulence\Api\DependencyResolutionException;
use Opulence\Api\IDependencyResolver;
use Opulence\Net\Http\Handlers\IRequestHandler;
use Opulence\Routing\Middleware\MiddlewareBinding;

/**
 * Defines the resolver of middleware request handlers
 */
class MiddlewareRequestHandlerResolver
{
    /** @var IDependencyResolver The dependency resolver */
    private $dependencyResolver;

    /**
     * @param IDependencyResolver $dependencyResolver The dependency resolver
     */
    public function __construct(IDependencyResolver $dependencyResolver)
    {
        $this->dependencyResolver = $dependencyResolver;
    }

    /**
     * Resolves a middleware binding to a request handler
     *
     * @param MiddlewareBinding $middlewareBinding The middleware binding to resolve
     * @param IRequestHandler $next The next request handler in the pipeline
     * @return MiddlewareRequestHandler The resolved middleware request handler
     * @throws InvalidArgumentException Thrown if the middleware does not implement IMiddleware
     * @throws DependencyResolutionException Thrown if the middleware could not be resolved
     */
    public function resolve(MiddlewareBinding $middlewareBinding, IRequestHandler $next): MiddlewareRequestHandler
    {
        $middleware = $this->dependencyResolver->resolve($middlewareBinding->className);

        if (!$middleware instanceof IMiddleware) {
            throw new InvalidArgumentException(
                sprintf('Middleware %s does not implement %s', \get_class($middleware), IMiddleware::class)
            );
        }

        if ($middleware instanceof AttributeMiddleware) {
            $middleware->setAttributes($middlewareBinding->attributes);
        }

        return new MiddlewareRequestHandler($middleware, $next);
    }
}