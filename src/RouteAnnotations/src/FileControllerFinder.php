<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\RouteAnnotations;

use Aphiria\Api\Controllers\Controller;
use Aphiria\Reflection\FileClassFinder;
use Aphiria\Reflection\IClassFinder;
use Doctrine\Annotations\Reader;
use ReflectionClass;

/**
 * Defines the class that finds controllers
 */
final class FileControllerFinder implements IControllerFinder
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
        // Filter out any non-concrete controller classes
        return array_filter($this->classFinder->findAllClasses($paths, true), function ($className) {
            $class = new ReflectionClass($className);

            // Allow either Aphiria controllers or classes with the controller annotation
            return
                (
                    $class->isSubclassOf(Controller::class)
                    || $this->annotationReader->getClassAnnotation($class, 'Controller') !== null
                )
                && !$class->isInterface()
                && !$class->isAbstract();
        });
    }
}
