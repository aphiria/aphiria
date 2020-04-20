<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Builders;

use Aphiria\Routing\IRouteRegistrant;
use Aphiria\Routing\RouteCollection;
use Closure;
use InvalidArgumentException;

/**
 * Defines the route collection builder registrant that uses route collection builders to register routes
 */
final class RouteCollectionBuilderRouteRegistrant implements IRouteRegistrant
{
    /** @var Closure[] The list of closures that take in a RouteCollectionBuilder instance and register routes */
    private array $routeCollectionBuilderClosures;

    /**
     * @param Closure[]|Closure $routeCollectionBuilderClosures The list of closures that take in a RouteCollectionBuilder instance and register routes
     * @throws InvalidArgumentException Thrown if the parameter was not a Closure nor list of Closures
     */
    public function __construct($routeCollectionBuilderClosures)
    {
        if (\is_array($routeCollectionBuilderClosures)) {
            $this->routeCollectionBuilderClosures = $routeCollectionBuilderClosures;
        } elseif ($routeCollectionBuilderClosures instanceof Closure) {
            $this->routeCollectionBuilderClosures = [$routeCollectionBuilderClosures];
        } else {
            throw new InvalidArgumentException('Closures must be an instance of ' . Closure::class . ' or an array of Closures');
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
