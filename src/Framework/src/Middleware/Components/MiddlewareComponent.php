<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Middleware\Components;

use Aphiria\Application\IComponent;
use Aphiria\DependencyInjection\IServiceResolver;
use Aphiria\Middleware\AttributeMiddleware;
use Aphiria\Middleware\IMiddleware;
use Aphiria\Middleware\MiddlewareBinding;
use Aphiria\Middleware\MiddlewareCollection;
use InvalidArgumentException;

/**
 * Defines the middleware component
 */
class MiddlewareComponent implements IComponent
{
    /** @var IServiceResolver The dependency resolver */
    private IServiceResolver $dependencyResolver;
    /** @var MiddlewareBinding[] The list of middleware bindings */
    private array $middleware = [];

    /**
     * @param IServiceResolver $dependencyResolver The dependency resolver
     */
    public function __construct(IServiceResolver $dependencyResolver)
    {
        $this->dependencyResolver = $dependencyResolver;
    }

    /**
     * @inheritdoc
     */
    public function build(): void
    {
        $middlewareCollection = $this->dependencyResolver->resolve(MiddlewareCollection::class);

        foreach ($this->middleware as $middlewareConfig) {
            $middlewareBinding = $middlewareConfig['middlewareBinding'];
            $middleware = $this->dependencyResolver->resolve($middlewareBinding->className);

            if (!$middleware instanceof IMiddleware) {
                throw new InvalidArgumentException(
                    sprintf('%s does not implement %s', get_class($middleware), IMiddleware::class)
                );
            }

            if ($middleware instanceof AttributeMiddleware) {
                $middleware->setAttributes($middlewareBinding->attributes);
            }

            $middlewareCollection->add($middleware, $middlewareConfig['priority']);
        }
    }

    /**
     * Adds global middleware to the collection
     *
     * @param MiddlewareBinding|MiddlewareBinding[] $middlewareBindings The middleware binding to add
     * @param int|null The optional priority to apply to the middleware (lower number => higher priority)
     * @return self For chaining
     */
    public function withGlobalMiddleware($middlewareBindings, int $priority = null): self
    {
        $middlewareBindings = is_array($middlewareBindings) ? $middlewareBindings : [$middlewareBindings];

        foreach ($middlewareBindings as $middlewareBinding) {
            $this->middleware[] = ['middlewareBinding' => $middlewareBinding, 'priority' => $priority];
        }

        return $this;
    }
}
