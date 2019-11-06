<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Bootstrappers;

use Aphiria\Reflection\FileClassFinder;
use Aphiria\Reflection\IClassFinder;
use InvalidArgumentException;
use ReflectionClass;

/**
 * Defines the class that can search through directories for bootstrapper classes
 */
final class FileBootstrapperFinder
{
    /** @var IClassFinder The class finder */
    private IClassFinder $classFinder;

    /**
     * @param IClassFinder|null $classFinder The class finder
     */
    public function __construct(IClassFinder $classFinder = null)
    {
        $this->classFinder = $classFinder ?? new FileClassFinder();
    }

    /**
     * Recursively finds all bootstrapper classes in the paths
     *
     * @param string|array $paths The path or list of paths to search
     * @return string[] The list of all bootstrapper class names
     * @throws InvalidArgumentException Thrown if the paths are not a string or array
     */
    public function findAll($paths): array
    {
        // Filter out any non-concrete bootstrapper classes
        return array_filter($this->classFinder->findAllClasses($paths, true), function ($className) {
            $reflectionClass = new ReflectionClass($className);

            return $reflectionClass->isSubclassOf(Bootstrapper::class) &&
                !$reflectionClass->isInterface() &&
                !$reflectionClass->isAbstract();
        });
    }
}
