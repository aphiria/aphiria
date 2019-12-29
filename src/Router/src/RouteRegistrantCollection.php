<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing;

/**
 * Defines a collection of route registrants that can be run in serial
 */
class RouteRegistrantCollection implements IRouteRegistrant
{
    /** @var IRouteRegistrant[] The list of route registrants */
    private array $routeRegistrants = [];

    /**
     * Adds a route registrant to the collection
     *
     * @param IRouteRegistrant $routeRegistrant The registrant to add
     */
    public function add(IRouteRegistrant $routeRegistrant): void
    {
        $this->routeRegistrants[] = $routeRegistrant;
    }

    /**
     * @inheritdoc
     */
    public function registerRoutes(RouteCollection $routes): void
    {
        foreach ($this->routeRegistrants as $routeRegistrant) {
            $routeRegistrant->registerRoutes($routes);
        }
    }
}
