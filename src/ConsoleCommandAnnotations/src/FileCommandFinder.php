<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ConsoleCommandAnnotations;

use Aphiria\Reflection\FileClassFinder;
use Aphiria\Reflection\IClassFinder;
use Doctrine\Annotations\Reader;
use ReflectionClass;

/**
 * Defines the command finder that scans for commands in files
 */
final class FileCommandFinder implements ICommandFinder
{
    /** @var Reader The annotation reader */
    private Reader $annotationReader;
    /** @var IClassFinder The class finder */
    private IClassFinder $classFinder;

    /**
     * @param Reader $annotationReader The annotation reader
     * @param IClassFinder|null $classFinder The class finder
     */
    public function __construct(Reader $annotationReader, IClassFinder $classFinder = null)
    {
        $this->annotationReader = $annotationReader;
        $this->classFinder = $classFinder ?? new FileClassFinder();
    }

    /**
     * @inheritdoc
     */
    public function findAll($paths): array
    {
        // Filter out any non-concrete command classes
        return array_filter($this->classFinder->findAllClasses($paths, true), function ($className) {
            $class = new ReflectionClass($className);

            return
                $this->annotationReader->getClassAnnotation($class, 'Command') !== null
                && !$class->isInterface()
                && !$class->isAbstract();
        });
    }
}
