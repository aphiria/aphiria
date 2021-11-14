<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Binders;

use Aphiria\Reflection\ITypeFinder;
use Aphiria\Reflection\TypeFinder;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;

/**
 * Defines the class that can search through directories for binder classes
 */
final class FileBinderFinder
{
    /**
     * @param ITypeFinder $classFinder The class finder
     */
    public function __construct(private readonly ITypeFinder $classFinder = new TypeFinder())
    {
    }

    /**
     * Recursively finds all binder classes in the paths
     *
     * @param string|list<string> $paths The path or list of paths to search
     * @return list<class-string<Binder>> The list of all binder class names
     * @throws InvalidArgumentException Thrown if the paths are not a string or array
     * @throws ReflectionException Thrown if a class could not be reflected
     */
    public function findAll(string|array $paths): array
    {
        // Filter out any non-concrete binder classes
        /** @psalm-suppress LessSpecificReturnStatement This will return a list of binder names */
        return \array_values(\array_filter($this->classFinder->findAllTypes($paths, true), static function ($className) {
            $reflectionClass = new ReflectionClass($className);

            return $reflectionClass->isSubclassOf(Binder::class) &&
                !$reflectionClass->isInterface() &&
                !$reflectionClass->isAbstract();
        }));
    }
}
