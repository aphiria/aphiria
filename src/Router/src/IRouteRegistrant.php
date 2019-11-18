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
 * Defines the interface for route registrants to implement
 */
interface IRouteRegistrant
{
    /**
     * Registers routes to the route collection
     *
     * @param RouteCollection $routes The route collection to add to
     */
    public function registerRoutes(RouteCollection $routes): void;
}
