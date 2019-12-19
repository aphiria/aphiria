<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints\Annotations;

use Aphiria\Reflection\ITypeFinder;
use Aphiria\Validation\Constraints\Annotations\AnnotationObjectConstraintRegistrant;
use Aphiria\Validation\Constraints\Annotations\Required;
use Aphiria\Validation\Constraints\ObjectConstraintRegistry;
use Aphiria\Validation\Constraints\RequiredConstraint;
use Doctrine\Annotations\AnnotationReader;
use Doctrine\Annotations\Reader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the annotation constraint registrant
 */
class AnnotationConstraintRegistrantTest extends TestCase
{
    private const PATH = __DIR__;
    private AnnotationObjectConstraintRegistrant $registrant;
    private Reader $reader;
    /** @var ITypeFinder|MockObject */
    private ITypeFinder $typeFinder;

    protected function setUp(): void
    {
        $this->reader = new AnnotationReader();
        $this->typeFinder = $this->createMock(ITypeFinder::class);
        $this->registrant = new AnnotationObjectConstraintRegistrant(self::PATH, $this->reader, $this->typeFinder);
    }

    public function testMethodsWithConstraintsAreRegistered(): void
    {
        $object = new class () {
            /**
             * @Required
             */
            public function method(): bool
            {
                return true;
            }
        };
        $this->typeFinder->expects($this->once())
            ->method('findAllClasses')
            ->with([self::PATH])
            ->willReturn([\get_class($object)]);
        $objectConstraints = new ObjectConstraintRegistry();
        $this->registrant->registerConstraints($objectConstraints);
        $this->assertCount(
            1,
            $objectConstraints->getConstraintsForClass(\get_class($object))->getMethodConstraints('method')
        );
        $this->assertInstanceOf(
            RequiredConstraint::class,
            $objectConstraints->getConstraintsForClass(\get_class($object))->getMethodConstraints('method')[0]
        );
    }

    public function testMethodsWithNonValidationConstraintAnnotationsAreNotRegistered(): void
    {
        $object = new class () {
            /**
             * @\Doctrine\Annotations\Annotation\Required
             */
            public function method(): bool
            {
                return true;
            }
        };
        $this->typeFinder->expects($this->once())
            ->method('findAllClasses')
            ->with([self::PATH])
            ->willReturn([\get_class($object)]);
        $objectConstraints = new ObjectConstraintRegistry();
        $this->registrant->registerConstraints($objectConstraints);
        $this->assertCount(
            0,
            $objectConstraints->getConstraintsForClass(\get_class($object))->getMethodConstraints('method')
        );
    }

    public function testPropertiesWithConstraintsAreRegistered(): void
    {
        $object = new class () {
            /**
             * @Required
             */
            public bool $prop = true;
        };
        $this->typeFinder->expects($this->once())
            ->method('findAllClasses')
            ->with([self::PATH])
            ->willReturn([\get_class($object)]);
        $objectConstraints = new ObjectConstraintRegistry();
        $this->registrant->registerConstraints($objectConstraints);
        $this->assertCount(
            1,
            $objectConstraints->getConstraintsForClass(\get_class($object))->getPropertyConstraints('prop')
        );
        $this->assertInstanceOf(
            RequiredConstraint::class,
            $objectConstraints->getConstraintsForClass(\get_class($object))->getPropertyConstraints('prop')[0]
        );
    }

    public function testPropertiesWithNonValidationConstraintAnnotationsAreNotRegistered(): void
    {
        $object = new class () {
            /**
             * @\Doctrine\Annotations\Annotation\Required
             */
            public bool $prop = true;
        };
        $this->typeFinder->expects($this->once())
            ->method('findAllClasses')
            ->with([self::PATH])
            ->willReturn([\get_class($object)]);
        $objectConstraints = new ObjectConstraintRegistry();
        $this->registrant->registerConstraints($objectConstraints);
        $this->assertCount(
            0,
            $objectConstraints->getConstraintsForClass(\get_class($object))->getPropertyConstraints('prop')
        );
    }
}
