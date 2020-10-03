<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Routing\Components;

use Aphiria\Api\Application;
use Aphiria\Api\Router;
use Aphiria\Application\IComponent;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\ResolutionException;
use Aphiria\DependencyInjection\TargetedContext;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Routing\Annotations\AnnotationRouteRegistrant;
use Aphiria\Routing\Builders\RouteCollectionBuilderRouteRegistrant;
use Aphiria\Routing\RouteCollection;
use Aphiria\Routing\RouteRegistrantCollection;
use Closure;
use RuntimeException;

/**
 * Defines the router component
 */
class RouterComponent implements IComponent
{
    /** @var IContainer The DI container */
    private IContainer $container;
    /** @var Closure[] The list of callbacks that can register route builders */
    private array $callbacks = [];
    /** @var bool Whether or not annotations are enabled */
    private bool $annotationsEnabled = false;

    /**
     * @param IContainer $container The DI container
     */
    public function __construct(IContainer $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritdoc
     * @throws ResolutionException Thrown if any dependencies could not be resolved
     */
    public function build(): void
    {
        $routeRegistrants = $this->container->resolve(RouteRegistrantCollection::class);

        if ($this->annotationsEnabled) {
            $annotationRouteRegistrant = null;

            if (!$this->container->tryResolve(AnnotationRouteRegistrant::class, $annotationRouteRegistrant)) {
                throw new RuntimeException(AnnotationRouteRegistrant::class . ' cannot be null if using annotations');
            }

            $routeRegistrants->add($annotationRouteRegistrant);
        }

        $routeRegistrants->add(new RouteCollectionBuilderRouteRegistrant($this->callbacks));
        $routeRegistrants->registerRoutes($this->container->resolve(RouteCollection::class));
        $this->container->for(
            new TargetedContext(Application::class),
            fn (IContainer $container) => $container->bindFactory(IRequestHandler::class, fn () => $this->container->resolve(Router::class))
        );
    }

    /**
     * Enables route annotations
     *
     * @return static For chaining
     */
    public function withAnnotations(): static
    {
        $this->annotationsEnabled = true;

        return $this;
    }

    /**
     * Adds routes to the registry
     *
     * @param Closure $callback The callback that takes in an instance of RouteBuilderRegistry
     * @return static For chaining
     */
    public function withRoutes(Closure $callback): static
    {
        $this->callbacks[] = $callback;

        return $this;
    }
}
