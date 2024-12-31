<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Routing;

use Aphiria\Net\Http\IRequest;
use InvalidArgumentException;
use OutOfBoundsException;

/**
 * Defines the interface for route request factories to implement
 */
interface IRouteRequestFactory
{
    /**
     * Creates a request for a route
     *
     * @param string $routeName The name of the route to create a request for
     * @param array<string, mixed> $routeVariables The route variable names to values to use
     * @param string|null $method The HTTP method to use (required if the route supports multiple HTTP methods)
     * @return IRequest The request
     * @throws OutOfBoundsException Thrown if the route does not exist
     * @throws RouteRequestCreationException Thrown if there was an error generating the request
     * @throws InvalidArgumentException Thrown if the the method is null and the route supports multiple methods
     */
    public function createRouteUri(string $routeName, array $routeVariables = [], ?string $method = null): IRequest;
}
