<?php

/**
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Regexes;

/**
 * Defines a list of group regexes that can be used for route matching
 */
class GroupRegexCollection
{
    /** @var array The list of methods to their various regexes */
    private $regexes = [
        'DELETE' => [],
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'HEAD' => [],
        'OPTIONS' => [],
        'PATCH' => []
    ];

    /**
     * Performs a deep clone of the regexes
     */
    public function __clone()
    {
        foreach ($this->regexes as $method => $regexesByMethod) {
            foreach ($regexesByMethod as $index => $groupRegex) {
                $this->regexes[$method][$index] = clone $groupRegex;
            }
        }
    }

    /**
     * Adds a regexes to the collection
     *
     * @param string $method The HTTP method to add the regex to
     * @param GroupRegex $regex The regex to add
     */
    public function add(string $method, GroupRegex $regex): void
    {
        $this->regexes[$method][] = $regex;
    }

    /**
     * Gets all the regexes for a particular HTTP method
     *
     * @param string The HTTP method whose routes we want
     * @return GroupRegex[] The list of group regexes
     */
    public function getByMethod(string $method): array
    {
        return $this->regexes[$method] ?? [];
    }
}
