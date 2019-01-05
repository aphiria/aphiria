<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers;

use Opulence\Routing\Route;

/**
 * Defines the result of an attempt to match a route
 */
class RouteMatchingResult
{
    /** @var bool Whether or not a match was found */
    public $matchFound;
    /** @var Route|null The matched route, if one was found, otherwise null */
    public $route;
    /** @var array The matched route variables */
    public $routeVariables;
    /** @var array The list of allowed routes if a match was found but did not support the input HTTP method */
    public $allowedMethods;

    /**
     * @param Route|null $route The matched route, if one was found, otherwise null
     * @param array $routeVariables The matched route variables
     * @param array $allowedMethods he list of allowed routes if a match was found but did not support the input HTTP method
     *      Only populated on an unsuccessful match
     */
    public function __construct(
        ?Route $route,
        array $routeVariables,
        array $allowedMethods
    ) {
        $this->matchFound = $route !== null;
        $this->route = $route;
        $this->routeVariables = $routeVariables;
        $this->allowedMethods = $allowedMethods;
    }
}