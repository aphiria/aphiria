<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates;

use OutOfBoundsException;

/**
 * Defines the interface for route URI factories to implement
 */
interface IRouteUriFactory
{
    /**
     * Creates a URI for a route
     *
     * @param string $routeName The name of the route to create a URI for
     * @param array $routeVars The route variable names to values to use
     * @return string The URI
     * @throws OutOfBoundsException Thrown if the route does not exist
     * @throws RouteUriCreationException Thrown if there was an error generating the URI
     */
    public function createRouteUri(string $routeName, array $routeVars = []): string;
}
