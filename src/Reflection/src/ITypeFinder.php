<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Reflection;

use InvalidArgumentException;
use ReflectionException;

/**
 * Defines the interface for type finders to implement
 */
interface ITypeFinder
{
    /**
     * Recursively finds all classes in the paths
     *
     * @param string|list<string> $directories The path or list of paths of directories to search
     * @param bool $recursive Whether or not we want to recurse through all directories
     * @param bool $includeAbstractClasses Whether or not to include abstract classes
     * @return list<class-string> The list of all class names
     * @throws InvalidArgumentException Thrown if the paths are not a string or array of strings
     */
    public function findAllClasses(string|array $directories, bool $recursive = false, bool $includeAbstractClasses = false): array;

    /**
     * Recursively finds all interfaces in the paths
     *
     * @param string|list<string> $directories The path or list of paths of directories to search
     * @param bool $recursive Whether or not we want to recurse through all directories
     * @return list<class-string> The list of all interface names
     * @throws InvalidArgumentException Thrown if the paths are not a string or array of strings
     */
    public function findAllInterfaces(string|array $directories, bool $recursive = false): array;

    /**
     * Recursively finds all sub-types of a particular type in a path
     *
     * @template T of object
     * @param class-string<T> $parentType The type whose sub-types we're searching for
     * @param string|list<string> $directories The path or list of paths of directories to search
     * @param bool $recursive Whether or not we want to recurse through all directories
     * @return list<class-string<T>> The list of all types that are sub-types of the input class/interface
     * @throws InvalidArgumentException Thrown if the paths are not a string or array of strings
     * @throws ReflectionException Thrown if any types could not be reflected in the input directories
     */
    public function findAllSubtypesOfType(string $parentType, string|array $directories, bool $recursive = false): array;

    /**
     * Recursively finds all types in the paths
     *
     * @param string|list<string> $directories The path or list of paths of directories to search
     * @param bool $recursive Whether or not we want to recurse through all directories
     * @return list<class-string> The list of all types
     * @throws InvalidArgumentException Thrown if the paths are not a string or array of strings
     */
    public function findAllTypes(string|array $directories, bool $recursive = false): array;
}
