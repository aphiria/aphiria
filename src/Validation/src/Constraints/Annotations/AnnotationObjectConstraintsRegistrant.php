<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints\Annotations;

use Aphiria\Reflection\ITypeFinder;
use Aphiria\Reflection\TypeFinder;
use Aphiria\Validation\Constraints\IObjectConstraintsRegistrant;
use Aphiria\Validation\Constraints\ObjectConstraints;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use ReflectionClass;

/**
 * Defines the constraint registrant for annotations
 */
final class AnnotationObjectConstraintsRegistrant implements IObjectConstraintsRegistrant
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
     */
    public function __construct($paths, Reader $annotationReader = null, ITypeFinder $typeFinder = null)
    {
        $this->paths = \is_array($paths) ? $paths : [$paths];
        $this->annotationReader = $annotationReader ?? new AnnotationReader();
        $this->typeFinder = $typeFinder ?? new TypeFinder();
    }

    /**
     * @inheritdoc
     */
    public function registerConstraints(ObjectConstraintsRegistry $objectConstraints): void
    {
        foreach ($this->typeFinder->findAllClasses($this->paths, true) as $class) {
            $reflectionClass = new ReflectionClass($class);
            $propertyConstraints = $methodConstraints = [];

            foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                foreach ($this->annotationReader->getPropertyAnnotations($reflectionProperty) as $annotation) {
                    if (!$annotation instanceof IConstraintAnnotation) {
                        continue;
                    }

                    $propertyName = $reflectionProperty->getName();

                    if (!isset($propertyConstraints[$propertyName])) {
                        $propertyConstraints[$propertyName] = [];
                    }

                    $propertyConstraints[$propertyName] = [
                        ...$propertyConstraints[$propertyName],
                        $annotation->createConstraintFromAnnotation()
                    ];
                }
            }

            foreach ($reflectionClass->getMethods() as $reflectionMethod) {
                foreach ($this->annotationReader->getMethodAnnotations($reflectionMethod) as $annotation) {
                    if (!$annotation instanceof IConstraintAnnotation) {
                        continue;
                    }

                    $methodName = $reflectionMethod->getName();

                    if (!isset($methodConstraints[$methodName])) {
                        $methodConstraints[$methodName] = [];
                    }

                    $methodConstraints[$methodName] = [
                        ...$methodConstraints[$methodName],
                        $annotation->createConstraintFromAnnotation()
                    ];
                }
            }

            $objectConstraints->registerObjectConstraints(new ObjectConstraints(
                $class,
                $propertyConstraints,
                $methodConstraints
            ));
        }
    }
}
