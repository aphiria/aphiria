<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ValidationAnnotations;

use Aphiria\Reflection\ITypeFinder;
use Aphiria\Reflection\TypeFinder;
use Aphiria\Validation\ConstraintRegistry;
use Aphiria\ValidationAnnotations\Annotations\IValidationConstraintAnnotation;
use Doctrine\Annotations\AnnotationException;
use Doctrine\Annotations\AnnotationReader;
use Doctrine\Annotations\Reader;
use ReflectionClass;
use ReflectionException;

/**
 * Defines the constraint registrant for annotations
 */
final class AnnotationConstraintRegistrant
{
    /** @var string[] The paths to check for constraints */
    private array $paths;
    /** @var Reader The annotation reader */
    private Reader $annotationReader;
    /** @var ITypeFinder The type finder */
    private ITypeFinder $typeFinder;

    /**
     * @param string|string[] $paths The path or paths to check for constraints
     * @param Reader|null $annotationReader The annotation reader
     * @param ITypeFinder|null $typeFinder The type finder
     * @throws AnnotationException Thrown if there was an error creating the annotation reader
     */
    public function __construct($paths, Reader $annotationReader = null, ITypeFinder $typeFinder = null)
    {
        $this->paths = \is_array($paths) ? $paths : [$paths];
        $this->annotationReader = $annotationReader ?? new AnnotationReader();
        $this->typeFinder = $typeFinder ?? new TypeFinder();
    }

    /**
     * Registers constraints from any annotations
     *
     * @param ConstraintRegistry $constraints The constraint registry to register to
     * @throws ReflectionException Thrown if a class could not be reflected
     */
    public function registerConstraints(ConstraintRegistry $constraints): void
    {
        foreach ($this->typeFinder->findAllClasses($this->paths, true) as $class) {
            $reflectionClass = new ReflectionClass($class);

            foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                foreach ($this->annotationReader->getPropertyAnnotations($reflectionProperty) as $annotation) {
                    if (!$annotation instanceof IValidationConstraintAnnotation) {
                        continue;
                    }

                    $constraints->registerPropertyConstraints(
                        $class,
                        $reflectionProperty->getName(),
                        $annotation->createConstraintFromAnnotation()
                    );
                }
            }

            foreach ($reflectionClass->getMethods() as $reflectionMethod) {
                foreach ($this->annotationReader->getMethodAnnotations($reflectionMethod) as $annotation) {
                    if (!$annotation instanceof IValidationConstraintAnnotation) {
                        continue;
                    }

                    $constraints->registerMethodConstraints(
                        $class,
                        $reflectionMethod->getName(),
                        $annotation->createConstraintFromAnnotation()
                    );
                }
            }
        }
    }
}
