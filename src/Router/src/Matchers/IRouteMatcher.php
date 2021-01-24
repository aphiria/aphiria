<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Matchers;

/**
 * Defines the interface for route matchers to implement
 */
interface IRouteMatcher
{
    /**
     * Tries to match a request to the configured routes
     *
     * @param string $httpMethod The HTTP method of the request
     * @param string $host The host of the request
     * @param string $path The path of the request
     * @param array<string, mixed> $headers The mapping of header names to values
     * @return RouteMatchingResult The result of the route matching
     */
    public function matchRoute(string $httpMethod, string $host, string $path, array $headers = []): RouteMatchingResult;
}
