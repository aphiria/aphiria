<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Reflection;

use InvalidArgumentException;

/**
 * Defines the interface for class finders to implement
 */
interface IClassFinder
{
    /**
     * Recursively finds all classes in the paths
     *
     * @param string|string[] $directories The path or list of paths of directories to search
     * @param bool $recursive Whether or not we want to recurse through all directories
     * @return string[] The list of all class names
     * @throws InvalidArgumentException Thrown if the paths are not a string or array of strings
     */
    public function findAllClasses($directories, bool $recursive = false): array;
}
