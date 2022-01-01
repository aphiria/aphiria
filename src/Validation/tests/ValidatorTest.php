<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests;

use Aphiria\Validation\CircularDependencyException;
use Aphiria\Validation\Constraints\IConstraint;
use Aphiria\Validation\Constraints\ObjectConstraints;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;
use Aphiria\Validation\ConstraintViolation;
use Aphiria\Validation\ErrorMessages\IErrorMessageInterpolator;
use Aphiria\Validation\ValidationException;
use Aphiria\Validation\Validator;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    private Validator $validator;
    private ObjectConstraintsRegistry $objectConstraints;
    private IErrorMessageInterpolator&MockObject $errorMessageInterpolator;

    protected function setUp(): void
    {
        $this->objectConstraints = new ObjectConstraintsRegistry();
        $this->errorMessageInterpolator = $this->createMock(IErrorMessageInterpolator::class);
        $this->validator = new Validator($this->objectConstraints, $this->errorMessageInterpolator);
    }

    public function testTryValidateMethodReturnsFalseForInvalidValue(): void
    {
        $object = new class () {
            public function method(): int
            {
                return 1;
            }
        };
        $constraints = [$this->createMockConstraint(false, 1)];
        $this->objectConstraints->registerObjectConstraints(new ObjectConstraints(
            $object::class,
            [],
            ['method' => $constraints]
        ));
        $this->assertFalse($this->validator->tryValidateMethod($object, 'method'));
    }

    public function testTryValidateMethodReturnsTrueForValidValue(): void
    {
        $object = new class () {
            public function method(): int
            {
                return 1;
            }
        };
        $constraints = [$this->createMockConstraint(true, 1)];
        $this->objectConstraints->registerObjectConstraints(new ObjectConstraints(
            $object::class,
            [],
            ['method' => $constraints]
        ));
        $this->assertTrue($this->validator->tryValidateMethod($object, 'method'));
    }

    public function testTryValidateMethodWillRecursivelyValidateObjects(): void
    {
        $innerObject = new class () {
            public int $prop = 1;
        };
        $outerObject = new class ($innerObject) {
            private object $innerObject;

            public function __construct(object $innerObject)
            {
                $this->innerObject = $innerObject;
            }

            public function method(): object
            {
                return $this->innerObject;
            }
        };
        $this->objectConstraints->registerObjectConstraints(new ObjectConstraints(
            $innerObject::class,
            ['prop' => $this->createMockConstraint(true, 1)],
            []
        ));
        $this->assertTrue($this->validator->tryValidateMethod($outerObject, 'method'));
    }

    public function testTryValidateMethodWithMagicMethodIsSkipped(): void
    {
        $object = new class () {
            public function __toString(): string
            {
                die('Should not get here');
            }
        };
        $this->assertTrue($this->validator->tryValidateMethod($object, '__toString'));
    }

    public function testTryValidateMethodWithRequiredParamsIsSkipped(): void
    {
        $object = new class () {
            public function foo(int $foo): string
            {
                die('Should not get here');
            }
        };
        $this->assertTrue($this->validator->tryValidateMethod($object, 'foo'));
    }

    public function testTryValidateMethodWithAPassedConstraintAndAFailedConstraintReturnsFalse(): void
    {
        $object = new class () {
            public function method(): int
            {
                return 1;
            }
        };
        $constraints = [
            $this->createMockConstraint(true, 1),
            $this->createMockConstraint(false, 1)
        ];
        $this->objectConstraints->registerObjectConstraints(new ObjectConstraints(
            $object::class,
            [],
            ['method' => $constraints]
        ));
        $this->assertFalse($this->validator->tryValidateMethod($object, 'method'));
    }

    public function testTryValidateMethodWithInvalidValuePopulatesConstraintViolations(): void
    {
        $object = new class () {
            public function method(): int
            {
                return 1;
            }
        };
        /** @var list<IConstraint> $constraints */
        $constraints = [$this->createMockConstraint(false, 1, )];
        $this->objectConstraints->registerObjectConstraints(new ObjectConstraints(
            $object::class,
            [],
            ['method' => $constraints]
        ));
        $this->errorMessageInterpolator->expects($this->once())
            ->method('interpolate')
            ->with($constraints[0]->getErrorMessageId(), $constraints[0]->getErrorMessagePlaceholders(1))
            ->willReturn('error');
        /** @var list<ConstraintViolation> $violations */
        $violations = [];
        $this->assertFalse($this->validator->tryValidateMethod($object, 'method', $violations));
        $this->assertCount(1, $violations);
        $this->assertSame('error', $violations[0]->errorMessage);
        $this->assertSame($constraints[0], $violations[0]->constraint);
        $this->assertEquals($object, $violations[0]->rootValue);
        $this->assertSame(1, $violations[0]->invalidValue);
    }

    public function testTryValidateMethodWithValidValueHasNoConstraintViolations(): void
    {
        $object = new class () {
            public function method(): int
            {
                return 1;
            }
        };
        $constraints = [$this->createMockConstraint(true, 1)];
        $this->objectConstraints->registerObjectConstraints(new ObjectConstraints(
            $object::class,
            [],
            ['method' => $constraints]
        ));
        $violations = [];
        $this->assertTrue($this->validator->tryValidateMethod($object, 'method', $violations));
        $this->assertCount(0, $violations);
    }

    public function testTryValidateObjectReturnsFalseForInvalidValue(): void
    {
        $object = new class () {
            public int $prop = 1;
        };
        $constraints = [$this->createMockConstraint(false, 1)];
        $this->objectConstraints->registerObjectConstraints(new ObjectConstraints(
            $object::class,
            ['prop' => $constraints],
            []
        ));
        $this->assertFalse($this->validator->tryValidateObject($object));
    }

    public function testTryValidateObjectReturnsTrueForValidValue(): void
    {
        $object = new class () {
            public int $prop = 1;
        };
        $constraints = [$this->createMockConstraint(true, 1)];
        $this->objectConstraints->registerObjectConstraints(new ObjectConstraints(
            $object::class,
            ['prop' => $constraints],
            []
        ));
        $this->assertTrue($this->validator->tryValidateObject($object));
    }

    public function testTryValidateObjectWillRecursivelyValidateObjects(): void
    {
        $innerObject = new class () {
            public int $prop = 1;
        };
        $outerObject = new class ($innerObject) {
            public object $innerObject;

            public function __construct(object $innerObject)
            {
                $this->innerObject = $innerObject;
            }
        };
        $this->objectConstraints->registerObjectConstraints(new ObjectConstraints(
            $innerObject::class,
            ['prop' => $this->createMockConstraint(true, 1)],
            []
        ));
        $this->assertTrue($this->validator->tryValidateObject($outerObject));
    }

    public function testTryValidateObjectWithAPassedConstraintAndAFailedConstraintReturnsFalse(): void
    {
        $object = new class () {
            public int $prop = 1;
        };
        $constraints = [
            $this->createMockConstraint(true, 1),
            $this->createMockConstraint(false, 1)
        ];
        $this->objectConstraints->registerObjectConstraints(new ObjectConstraints(
            $object::class,
            ['prop' => $constraints],
            []
        ));
        $this->assertFalse($this->validator->tryValidateObject($object));
    }

    public function testTryValidateObjectWithInvalidValueSetsConstraintViolations(): void
    {
        $object = new class () {
            public int $prop = 1;
        };
        /** @var list<IConstraint> $constraints */
        $constraints = [$this->createMockConstraint(false, 1)];
        $this->objectConstraints->registerObjectConstraints(new ObjectConstraints(
            $object::class,
            ['prop' => $constraints],
            []
        ));
        $this->errorMessageInterpolator->expects($this->once())
            ->method('interpolate')
            ->with($constraints[0]->getErrorMessageId(), $constraints[0]->getErrorMessagePlaceholders(1))
            ->willReturn('error');
        /** @var list<ConstraintViolation> $violations */
        $violations = [];
        $this->assertFalse($this->validator->tryValidateObject($object, $violations));
        $this->assertCount(1, $violations);
        $this->assertSame('error', $violations[0]->errorMessage);
        $this->assertSame($constraints[0], $violations[0]->constraint);
        $this->assertEquals($object, $violations[0]->rootValue);
        $this->assertSame(1, $violations[0]->invalidValue);
    }

    public function testTryValidatePropertyReturnsFalseForInvalidValue(): void
    {
        $object = new class () {
            public int $prop = 1;
        };
        $constraints = [$this->createMockConstraint(false, 1)];
        $this->objectConstraints->registerObjectConstraints(new ObjectConstraints(
            $object::class,
            ['prop' => $constraints],
            []
        ));
        $this->assertFalse($this->validator->tryValidateProperty($object, 'prop'));
    }

    public function testTryValidatePropertyReturnsTrueForValidValue(): void
    {
        $object = new class () {
            public int $prop = 1;
        };
        $constraints = [$this->createMockConstraint(true, 1)];
        $this->objectConstraints->registerObjectConstraints(new ObjectConstraints(
            $object::class,
            ['prop' => $constraints],
            []
        ));
        $this->assertTrue($this->validator->tryValidateProperty($object, 'prop'));
    }

    public function testTryValidateValueReturnsFalseForInvalidValue(): void
    {
        $constraints = [$this->createMockConstraint(false, 'foo')];
        $this->assertFalse($this->validator->tryValidateValue('foo', $constraints));
    }

    public function testTryValidateValueReturnsTrueForValidValue(): void
    {
        $constraints = [$this->createMockConstraint(true, 'foo')];
        $this->assertTrue($this->validator->tryValidateValue('foo', $constraints));
    }

    public function testTryValidatePropertyWillRecursivelyValidateObjects(): void
    {
        $innerObject = new class () {
            public int $prop = 1;
        };
        $outerObject = new class ($innerObject) {
            public object $innerObject;

            public function __construct(object $innerObject)
            {
                $this->innerObject = $innerObject;
            }
        };
        $this->objectConstraints->registerObjectConstraints(new ObjectConstraints(
            $innerObject::class,
            ['prop' => $this->createMockConstraint(true, 1)],
            []
        ));
        $this->assertTrue($this->validator->tryValidateProperty($outerObject, 'innerObject'));
    }

    public function testTryValidatePropertyWithAPassedConstraintAndAFailedConstraintReturnsFalse(): void
    {
        $object = new class () {
            public int $prop = 1;
        };
        $constraints = [
            $this->createMockConstraint(true, 1),
            $this->createMockConstraint(false, 1)
        ];
        $this->objectConstraints->registerObjectConstraints(new ObjectConstraints(
            $object::class,
            ['prop' => $constraints],
            []
        ));
        $this->assertFalse($this->validator->tryValidateProperty($object, 'prop'));
    }

    public function testTryValidatePropertyWithInvalidValueSetsConstraintViolations(): void
    {
        $object = new class () {
            public int $prop = 1;
        };
        /** @var list<IConstraint> $constraints */
        $constraints = [$this->createMockConstraint(false, 1)];
        $this->objectConstraints->registerObjectConstraints(new ObjectConstraints(
            $object::class,
            ['prop' => $constraints],
            []
        ));
        $this->errorMessageInterpolator->expects($this->once())
            ->method('interpolate')
            ->with($constraints[0]->getErrorMessageId(), $constraints[0]->getErrorMessagePlaceholders(1))
            ->willReturn('error');
        /** @var list<ConstraintViolation> $violations */
        $violations = [];
        $this->assertFalse($this->validator->tryValidateProperty($object, 'prop', $violations));
        $this->assertCount(1, $violations);
        $this->assertSame('error', $violations[0]->errorMessage);
        $this->assertSame($constraints[0], $violations[0]->constraint);
        $this->assertEquals($object, $violations[0]->rootValue);
        $this->assertSame(1, $violations[0]->invalidValue);
    }

    public function testTryValidateValueWithInvalidValueSetsConstraintViolations(): void
    {
        $constraints = [$this->createMockConstraint(false, 'foo')];
        /** @var list<ConstraintViolation> $violations */
        $violations = [];
        $this->assertFalse($this->validator->tryValidateValue('foo', $constraints, $violations));
        $this->assertCount(1, $violations);
        $this->assertSame($constraints[0], $violations[0]->constraint);
        $this->assertSame('foo', $violations[0]->rootValue);
        $this->assertSame('foo', $violations[0]->invalidValue);
    }

    public function testTryValidateValueWithValidValueHasNoConstraintViolations(): void
    {
        $constraints = [$this->createMockConstraint(true, 'foo')];
        $violations = [];
        $this->assertTrue($this->validator->tryValidateValue('foo', $constraints, $violations));
        $this->assertCount(0, $violations);
    }

    public function testValidateMethodWithCircularDependencyThrowsException(): void
    {
        $object1 = new class () {
            public ?object $methodReturnValue = null;

            public function method(): ?object
            {
                return $this->methodReturnValue;
            }
        };
        $object2 = new class () {
            public ?object $methodReturnValue = null;

            public function method(): ?object
            {
                return $this->methodReturnValue;
            }
        };
        $object1->methodReturnValue = $object2;
        $object2->methodReturnValue = $object1;
        $this->expectException(CircularDependencyException::class);
        $this->expectExceptionMessage('Circular dependency on ' . $object2::class . ' detected');
        $this->validator->validateMethod($object1, 'method');
    }

    public function testValidateObjectWithCircularDependencyThrowsException(): void
    {
        $object1 = new class () {
            public ?object $prop = null;
        };
        $object2 = new class () {
            public ?object $prop = null;
        };
        $object1->prop = $object2;
        $object2->prop = $object1;
        $this->expectException(CircularDependencyException::class);
        // Due to the order that objects are recursively validated, object1 will show up as the circular dependency
        $this->expectExceptionMessage('Circular dependency on ' . $object1::class . ' detected');
        $this->validator->validateObject($object1);
    }

    public function testValidateMethodThatDoesNotExistThrowsException(): void
    {
        $class = new class () {
        };
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($class::class . '::foo() does not exist');
        $this->validator->validateMethod($class, 'foo');
    }

    public function testValidateMethodThrowsIfInvalid(): void
    {
        $class = new class () {
            public function foo(): string
            {
                return 'foo';
            }
        };
        $this->expectException(ValidationException::class);
        $constraint = $this->createMock(IConstraint::class);
        $constraint->expects($this->once())
            ->method('passes')
            ->with('foo')
            ->willReturn(false);
        $this->objectConstraints->registerObjectConstraints(new ObjectConstraints(
            $class::class,
            [],
            ['foo' => [$constraint]]
        ));
        $this->validator->validateMethod($class, 'foo');
    }

    public function testValidatePropertyThatDoesNotExistThrowsException(): void
    {
        $class = new class () {
        };
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($class::class . '::foo does not exist');
        $this->validator->validateProperty($class, 'foo');
    }

    public function testValidatePropertyThrowsIfInvalid(): void
    {
        $class = new class () {
            public string $foo = 'foo';
        };
        $this->expectException(ValidationException::class);
        $constraint = $this->createMock(IConstraint::class);
        $constraint->expects($this->once())
            ->method('passes')
            ->with('foo')
            ->willReturn(false);
        $this->objectConstraints->registerObjectConstraints(new ObjectConstraints(
            $class::class,
            ['foo' => [$constraint]],
            []
        ));
        $this->validator->validateProperty($class, 'foo');
    }

    public function testValidatePropertyWithCircularDependencyThrowsException(): void
    {
        $object1 = new class () {
            public ?object $prop = null;
        };
        $object2 = new class () {
            public ?object $prop = null;
        };
        $object1->prop = $object2;
        $object2->prop = $object1;
        $this->expectException(CircularDependencyException::class);
        $this->expectExceptionMessage('Circular dependency on ' . $object2::class . ' detected');
        $this->validator->validateProperty($object1, 'prop');
    }

    public function testValidateValueThrowsIfInvalid(): void
    {
        $this->expectException(ValidationException::class);
        $constraint = $this->createMock(IConstraint::class);
        $constraint->expects($this->once())
            ->method('passes')
            ->with('foo')
            ->willReturn(false);
        $this->validator->validateValue('foo', [$constraint]);
    }

    /**
     * Creates a mock constraint
     *
     * @param bool $shouldPass Whether or not the constraint should pass
     * @param mixed $value The value that will be passed
     * @return IConstraint The created constraint
     */
    private function createMockConstraint(bool $shouldPass, mixed $value): IConstraint
    {
        $constraint = $this->createMock(IConstraint::class);
        $constraint->expects($this->once())
            ->method('passes')
            ->with($value)
            ->willReturn($shouldPass);

        return $constraint;
    }
}
