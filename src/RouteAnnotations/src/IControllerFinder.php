<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

namespace Aphiria\RouteAnnotations;

use InvalidArgumentException;

/**
 * Defines interface for controller finders to implement
 */
interface IControllerFinder
{
    /**
     * Recursively finds all controller classes in the paths
     *
     * @param string|array $paths The path or list of paths to search
     * @return string[] The list of all controller class names
     * @throws InvalidArgumentException Thrown if the paths are not a string or array
     */
    public function findAll($paths): array;
}
