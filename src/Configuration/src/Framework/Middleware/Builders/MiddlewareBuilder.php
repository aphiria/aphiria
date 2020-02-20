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
    private MiddlewareCollection $middlewareCollection;
    /** @var IDependencyResolver The dependency resolver */
    private IDependencyResolver $dependencyResolver;
    /** @var MiddlewareBinding[] The list of middleware bindings */
    private array $middlewareBindings = [];

    /**
     * @param MiddlewareCollection $middlewareCollection The list of middleware
     * @param IDependencyResolver $dependencyResolver The dependency resolver
     */
    public function __construct(MiddlewareCollection $middlewareCollection, IDependencyResolver $dependencyResolver)
    {
        $this->middlewareCollection = $middlewareCollection;
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

            $this->middlewareCollection->add($middleware);
        }
    }

    /**
     * Adds global middleware to the collection
     *
     * @param MiddlewareBinding|MiddlewareBinding[] $middlewareBindings The middleware binding to add
     * @return MiddlewareBuilder For chaining
     */
    public function withGlobalMiddleware(MiddlewareBinding $middlewareBindings): self
    {
        if ($middlewareBindings instanceof MiddlewareBinding) {
            $this->middlewareBindings[] = $middlewareBindings;
        } elseif (\is_array($middlewareBindings)) {
            $this->middlewareBindings = [...$this->middlewareBindings, ...$middlewareBindings];
        }

        return $this;
    }
}
