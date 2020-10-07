<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints\Attributes;

use Aphiria\Reflection\ITypeFinder;
use Aphiria\Validation\Constraints\Attributes\AttributeObjectConstraintsRegistrant;
use Aphiria\Validation\Constraints\Attributes\Required;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;
use Aphiria\Validation\Constraints\RequiredConstraint;
use Aphiria\Validation\Tests\Constraints\Attributes\Mocks\NonConstraintAttribute;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AttributeConstraintsRegistrantTest extends TestCase
{
    private const PATH = __DIR__;
    private AttributeObjectConstraintsRegistrant $registrant;
    private ITypeFinder|MockObject $typeFinder;

    protected function setUp(): void
    {
        $this->typeFinder = $this->createMock(ITypeFinder::class);
        $this->registrant = new AttributeObjectConstraintsRegistrant(self::PATH, $this->typeFinder);
    }

    public function testMethodsWithConstraintsAreRegistered(): void
    {
        $object = new class() {
            #[Required]
            public function method(): bool
            {
                return true;
            }
        };
        $this->typeFinder->expects($this->once())
            ->method('findAllClasses')
            ->with([self::PATH])
            ->willReturn([$object::class]);
        $objectConstraints = new ObjectConstraintsRegistry();
        $this->registrant->registerConstraints($objectConstraints);
        $methodConstraints = $objectConstraints->getConstraintsForClass($object::class)->getMethodConstraints('method');
        $this->assertCount(1, $methodConstraints);
        $this->assertInstanceOf(RequiredConstraint::class, $methodConstraints[0]);
    }

    public function testMethodsWithNonValidationConstraintAttributesAreNotRegistered(): void
    {
        $object = new class() {
            #[NonConstraintAttribute]
            public function method(): bool
            {
                return true;
            }
        };
        $this->typeFinder->expects($this->once())
            ->method('findAllClasses')
            ->with([self::PATH])
            ->willReturn([$object::class]);
        $objectConstraints = new ObjectConstraintsRegistry();
        $this->registrant->registerConstraints($objectConstraints);
        $this->assertCount(0, $objectConstraints->getConstraintsForClass($object::class)->getMethodConstraints('method'));
    }

    public function testPropertiesWithConstraintsAreRegistered(): void
    {
        $object = new class() {
            #[Required]
            public bool $prop = true;
        };
        $this->typeFinder->expects($this->once())
            ->method('findAllClasses')
            ->with([self::PATH])
            ->willReturn([$object::class]);
        $objectConstraints = new ObjectConstraintsRegistry();
        $this->registrant->registerConstraints($objectConstraints);
        $propertyConstraints = $objectConstraints->getConstraintsForClass($object::class)->getPropertyConstraints('prop');
        $this->assertCount(1, $propertyConstraints);
        $this->assertInstanceOf(RequiredConstraint::class, $propertyConstraints[0]);
    }

    public function testPropertiesWithNonValidationConstraintAttributesAreNotRegistered(): void
    {
        $object = new class() {
            #[NonConstraintAttribute]
            public bool $prop = true;
        };
        $this->typeFinder->expects($this->once())
            ->method('findAllClasses')
            ->with([self::PATH])
            ->willReturn([$object::class]);
        $objectConstraints = new ObjectConstraintsRegistry();
        $this->registrant->registerConstraints($objectConstraints);
        $this->assertCount(0, $objectConstraints->getConstraintsForClass($object::class)->getPropertyConstraints('prop'));
    }
}
