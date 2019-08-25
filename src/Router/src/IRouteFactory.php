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
 * Defines the interface for route factories to implement
 */
interface IRouteFactory
{
    /**
     * Creates a list of routes
     *
     * @return RouteCollection The created routes
     */
    public function createRoutes(): RouteCollection;
}
