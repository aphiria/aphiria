<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Tests\Constraints;

use Aphiria\Validation\Constraints\EachConstraint;
use Aphiria\Validation\Constraints\IValidationConstraint;
use Aphiria\Validation\ValidationContext;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the each constraint
 */
class EachConstraintTest extends TestCase
{
    public function testMultipleConstraintsAreAccepted(): void
    {
        $expectedContext = new ValidationContext('foo');
        $constraint1 = $this->createMock(IValidationConstraint::class);
        $constraint1->expects($this->once())
            ->method('passes')
            ->with('foo', $expectedContext)
            ->willReturn(true);
        $constraint2 = $this->createMock(IValidationConstraint::class);
        $constraint2->expects($this->once())
            ->method('passes')
            ->with('foo', $expectedContext)
            ->willReturn(true);
        $eachConstraint = new EachConstraint([$constraint1, $constraint2], 'foo');
        $this->assertTrue($eachConstraint->passes(['foo'], new ValidationContext('foo')));
    }

    public function testPassesOnNonIterableValueThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value must be iterable');
        $eachConstraint = new EachConstraint($this->createMock(IValidationConstraint::class), 'foo');
        $eachConstraint->passes('foo', new ValidationContext('foo'));
    }

    public function testPassesOnEmptyValueReturnsTrue(): void
    {
        $constraint = $this->createMock(IValidationConstraint::class);
        $constraint->expects($this->never())
            ->method('passes');
        $eachConstraint = new EachConstraint($constraint, 'foo');
        $this->assertTrue($eachConstraint->passes([], new ValidationContext('foo')));
    }

    public function testPassesOnAllPassedConstraintsReturnsTrue(): void
    {
        $expectedContext = new ValidationContext('foo');
        $constraint1 = $this->createMock(IValidationConstraint::class);
        $constraint1->expects($this->once())
            ->method('passes')
            ->with('foo', $expectedContext)
            ->willReturn(true);
        $constraint2 = $this->createMock(IValidationConstraint::class);
        $constraint2->expects($this->once())
            ->method('passes')
            ->with('foo', $expectedContext)
            ->willReturn(true);
        $eachConstraint = new EachConstraint([$constraint1, $constraint2], 'foo');
        $this->assertTrue($eachConstraint->passes(['foo'], new ValidationContext('foo')));
    }

    public function testPassesOnFailedConstraintDoesNotCallSecondConstraint(): void
    {
        $expectedContext = new ValidationContext('foo');
        $constraint1 = $this->createMock(IValidationConstraint::class);
        $constraint1->expects($this->once())
            ->method('passes')
            ->with('foo', $expectedContext)
            ->willReturn(false);
        $constraint2 = $this->createMock(IValidationConstraint::class);
        $constraint2->expects($this->never())
            ->method('passes');
        $eachConstraint = new EachConstraint([$constraint1, $constraint2], 'foo');
        $this->assertFalse($eachConstraint->passes(['foo'], new ValidationContext('foo')));
    }

    public function testPassesOnPassedAndFailedConstraintsReturnsFalse(): void
    {
        $expectedContext = new ValidationContext('foo');
        $constraint1 = $this->createMock(IValidationConstraint::class);
        $constraint1->expects($this->once())
            ->method('passes')
            ->with('foo', $expectedContext)
            ->willReturn(true);
        $constraint2 = $this->createMock(IValidationConstraint::class);
        $constraint2->expects($this->once())
            ->method('passes')
            ->with('foo', $expectedContext)
            ->willReturn(false);
        $eachConstraint = new EachConstraint([$constraint1, $constraint2], 'foo');
        $this->assertFalse($eachConstraint->passes(['foo'], new ValidationContext('foo')));
    }

    public function testSingleConstraintIsAccepted(): void
    {
        $expectedContext = new ValidationContext('foo');
        $constraint = $this->createMock(IValidationConstraint::class);
        $constraint->expects($this->once())
            ->method('passes')
            ->with('foo', $expectedContext)
            ->willReturn(true);
        $eachConstraint = new EachConstraint($constraint, 'foo');
        $this->assertTrue($eachConstraint->passes(['foo'], new ValidationContext('foo')));
    }
}
