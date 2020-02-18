<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Configuration\Framework\Middleware\Builders;

use Aphiria\Configuration\Builders\IApplicationBuilder;
use Aphiria\Configuration\Builders\IComponentBuilder;
use Aphiria\Configuration\Middleware\MiddlewareBinding;
use Aphiria\DependencyInjection\IDependencyResolver;
use Aphiria\Middleware\AttributeMiddleware;
use Aphiria\Middleware\IMiddleware;
use Aphiria\Middleware\MiddlewareCollection;
use InvalidArgumentException;

/**
 * Defines the middleware component builder
 */
final class MiddlewareBuilder implements IComponentBuilder
{
    /** @var MiddlewareCollection The list of middleware */
    private MiddlewareCollection $middleware;
    /** @var IDependencyResolver The dependency resolver */
    private IDependencyResolver $dependencyResolver;
    /** @var MiddlewareBinding[] The list of middleware bindings */
    private array $middlewareBindings = [];

    /**
     * @param MiddlewareCollection $middleware The list of middleware
     * @param IDependencyResolver $dependencyResolver The dependency resolver
     */
    public function __construct(MiddlewareCollection $middleware, IDependencyResolver $dependencyResolver)
    {
        $this->middleware = $middleware;
        $this->dependencyResolver = $dependencyResolver;
    }

    /**
     * @inheritdoc
     */
    public function build(IApplicationBuilder $appBuilder): void
    {
        foreach ($this->middlewareBindings as $middlewareBinding) {
            $middleware = $this->dependencyResolver->resolve($middlewareBinding->className);

            if (!$middleware instanceof IMiddleware) {
                throw new InvalidArgumentException(
                    sprintf('Middleware %s does not implement %s', get_class($middleware), IMiddleware::class)
                );
            }

            if ($middleware instanceof AttributeMiddleware) {
                $middleware->setAttributes($middlewareBinding->attributes);
            }

            $this->middleware->add($middleware);
        }
    }

    /**
     * Adds a middleware to the collection
     *
     * @param MiddlewareBinding[] $middlewareBinding The middleware bindings to add
     * @return MiddlewareBuilder For chaining
     */
    public function withManyMiddlewareBindings(array $middlewareBinding): self
    {
        $this->middlewareBindings = [...$this->middlewareBindings, ...$middlewareBinding];

        return $this;
    }

    /**
     * Adds a middleware to the collection
     *
     * @param MiddlewareBinding $middlewareBinding The middleware binding to add
     * @return MiddlewareBuilder For chaining
     */
    public function withMiddlewareBinding(MiddlewareBinding $middlewareBinding): self
    {
        $this->middlewareBindings[] = $middlewareBinding;

        return $this;
    }
}
