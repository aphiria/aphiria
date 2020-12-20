<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
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
    /** @var bool|null Whether or not the request method was allowed, or null if no match was found */
    public ?bool $methodIsAllowed;

    /**
     * @param Route|null $route The matched route, if one was found, otherwise null
     * @param array<string, mixed> $routeVariables The matched route variables
     * @param string[] $allowedMethods he list of allowed routes if a match was found but did not support the input HTTP method
     *      Only populated on an unsuccessful match
     */
    public function __construct(
        public ?Route $route,
        public array $routeVariables,
        public array $allowedMethods = []
    ) {
        $this->matchFound = $this->route !== null;

        if ($this->matchFound) {
            $this->methodIsAllowed = true;
        } elseif (\count($this->allowedMethods) === 0) {
            $this->methodIsAllowed = null;
        } else {
            $this->methodIsAllowed = false;
        }
    }
}
