<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Builders;

use Aphiria\Routing\IRouteRegistrant;
use Aphiria\Routing\RouteCollection;
use Closure;

/**
 * Defines the route collection builder registrant that uses route collection builders to register routes
 */
final class RouteCollectionBuilderRouteRegistrant implements IRouteRegistrant
{
    /** @var list<Closure(RouteCollectionBuilder): void> The list of closures that take in a RouteCollectionBuilder instance and register routes */
    private array $routeCollectionBuilderClosures;

    /**
     * @param list<Closure(RouteCollectionBuilder): void>|Closure(RouteCollectionBuilder): void $routeCollectionBuilderClosures The list of closures that take in a RouteCollectionBuilder instance and register routes
     */
    public function __construct(Closure|array $routeCollectionBuilderClosures)
    {
        if (\is_array($routeCollectionBuilderClosures)) {
            $this->routeCollectionBuilderClosures = $routeCollectionBuilderClosures;
        } else {
            $this->routeCollectionBuilderClosures = [$routeCollectionBuilderClosures];
        }
    }

    /**
     * @inheritdoc
     */
    public function registerRoutes(RouteCollection $routes): void
    {
        $routeBuilders = new RouteCollectionBuilder();

        foreach ($this->routeCollectionBuilderClosures as $closure) {
            $closure($routeBuilders);
        }

        $routes->addMany($routeBuilders->build()->getAll());
    }
}
