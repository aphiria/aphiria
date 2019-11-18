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
 * Defines a route registrant that aggregates several other route registrants
 */
class AggregateRouteRegistrant implements IRouteRegistrant
{
    /** @var IRouteRegistrant[] The list of route registrants */
    protected array $routeRegistrants = [];

    /**
     * Adds a route registrant to the collection
     *
     * @param IRouteRegistrant $routeRegistrant The registrant to add
     */
    public function addRouteRegistrant(IRouteRegistrant $routeRegistrant): void
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
