<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

namespace Aphiria\ConsoleCommandAnnotations;

/**
 * Defines the interface command finders to implement
 */
interface ICommandFinder
{
    /**
     * Finds all command classes
     *
     * @param string|string[] $paths The paths to search through
     * @return string[] The list of all command classes
     */
    public function findAll($paths): array;
}
