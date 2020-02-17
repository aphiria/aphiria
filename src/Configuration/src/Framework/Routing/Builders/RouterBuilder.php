<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Configuration\Framework\Routing\Builders;

use Aphiria\Configuration\Builders\IApplicationBuilder;
use Aphiria\Configuration\Builders\IComponentBuilder;
use Aphiria\Routing\Annotations\AnnotationRouteRegistrant;
use Aphiria\Routing\Builders\RouteBuilderRouteRegistrant;
use Aphiria\Routing\RouteCollection;
use Aphiria\Routing\RouteRegistrantCollection;
use Closure;
use RuntimeException;

/**
 * Defines the router builder
 */
final class RouterBuilder implements IComponentBuilder
{
    /** @var RouteCollection The list of routes to add to */
    private RouteCollection $routes;
    /** @var RouteRegistrantCollection The list of route registrants */
    private RouteRegistrantCollection $routeRegistrants;
    /** @var AnnotationRouteRegistrant|null The optional annotation route registrant */
    private ?AnnotationRouteRegistrant $annotationRouteRegistrant;
    /** @var Closure[] The list of callbacks that can register route builders */
    private array $callbacks = [];

    /**
     * @param RouteCollection $routes The list of routes to add to
     * @param RouteRegistrantCollection $routeRegistrants The list of route registrants
     * @param AnnotationRouteRegistrant|null $annotationRouteRegistrant The optional annotation route registrant
     */
    public function __construct(
        RouteCollection $routes,
        RouteRegistrantCollection $routeRegistrants,
        AnnotationRouteRegistrant $annotationRouteRegistrant = null
    ) {
        $this->routes = $routes;
        $this->routeRegistrants = $routeRegistrants;
        $this->annotationRouteRegistrant = $annotationRouteRegistrant;
    }

    /**
     * @inheritdoc
     */
    public function build(IApplicationBuilder $appBuilder): void
    {
        $this->routeRegistrants->add(new RouteBuilderRouteRegistrant($this->callbacks));
        $this->routeRegistrants->registerRoutes($this->routes);
    }

    /**
     * Enables route annotations
     *
     * @return RouterBuilder For chaining
     * @throws RuntimeException Thrown if the annotation route registrant was not set
     */
    public function withAnnotations(): self
    {
        if ($this->annotationRouteRegistrant === null) {
            throw new RuntimeException(AnnotationRouteRegistrant::class . ' cannot be null if using annotations');
        }

        $this->routeRegistrants->add($this->annotationRouteRegistrant);

        return $this;
    }

    /**
     * Adds routes to the registry
     *
     * @param Closure $callback The callback that takes in an instance of RouteBuilderRegistry
     * @return RouterBuilder For chaining
     */
    public function withRoutes(Closure $callback): self
    {
        $this->callbacks[] = $callback;

        return $this;
    }
}
