<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Matchers;

use Aphiria\Routing\Route;

/**
 * Defines the result of an attempt to match a route
 */
final class RouteMatchingResult
{
    /** @var bool Whether or not a match was found */
    public bool $matchFound;
    /** @var Route|null The matched route, if one was found, otherwise null */
    public ?Route $route;
    /** @var array The matched route variables */
    public array $routeVariables;
    /** @var bool|null Whether or not the request method was allowed, or null if no match was found */
    public ?bool $methodIsAllowed;
    /** @var array The list of allowed routes if a match was found but did not support the input HTTP method */
    public array $allowedMethods;

    /**
     * @param Route|null $route The matched route, if one was found, otherwise null
     * @param array $routeVariables The matched route variables
     * @param array $allowedMethods he list of allowed routes if a match was found but did not support the input HTTP method
     *      Only populated on an unsuccessful match
     */
    public function __construct(
        ?Route $route,
        array $routeVariables,
        array $allowedMethods = []
    ) {
        $this->route = $route;
        $this->matchFound = $this->route !== null;
        $this->routeVariables = $routeVariables;
        $this->allowedMethods = $allowedMethods;

        if ($this->matchFound) {
            $this->methodIsAllowed = true;
        } elseif (\count($this->allowedMethods) === 0) {
            $this->methodIsAllowed = null;
        } else {
            $this->methodIsAllowed = false;
        }
    }
}
