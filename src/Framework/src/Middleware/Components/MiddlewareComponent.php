<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Middleware\Components;

use Aphiria\Application\IComponent;
use Aphiria\DependencyInjection\IServiceResolver;
use Aphiria\DependencyInjection\ResolutionException;
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
    /** @var IServiceResolver The service resolver */
    private IServiceResolver $serviceResolver;
    /** @var MiddlewareBinding[] The list of middleware bindings */
    private array $middleware = [];

    /**
     * @param IServiceResolver $serviceResolver The service resolver
     */
    public function __construct(IServiceResolver $serviceResolver)
    {
        $this->serviceResolver = $serviceResolver;
    }

    /**
     * @inheritdoc
     * @throws ResolutionException Thrown if any dependencies could not be resolved
     */
    public function build(): void
    {
        $middlewareCollection = $this->serviceResolver->resolve(MiddlewareCollection::class);

        foreach ($this->middleware as $middlewareConfig) {
            $middlewareBinding = $middlewareConfig['middlewareBinding'];
            $middleware = $this->serviceResolver->resolve($middlewareBinding->className);

            if (!$middleware instanceof IMiddleware) {
                throw new InvalidArgumentException(
                    sprintf('%s does not implement %s', $middleware::class, IMiddleware::class)
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
    public function withGlobalMiddleware(MiddlewareBinding|array $middlewareBindings, int $priority = null): self
    {
        $middlewareBindings = \is_array($middlewareBindings) ? $middlewareBindings : [$middlewareBindings];

        foreach ($middlewareBindings as $middlewareBinding) {
            $this->middleware[] = ['middlewareBinding' => $middlewareBinding, 'priority' => $priority];
        }

        return $this;
    }
}
