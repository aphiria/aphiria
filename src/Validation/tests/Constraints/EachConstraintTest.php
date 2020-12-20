<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints;

use Aphiria\Validation\Constraints\EachConstraint;
use Aphiria\Validation\Constraints\IConstraint;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class EachConstraintTest extends TestCase
{
    public function testGettingErrorMessagePlaceholdersIncludesValue(): void
    {
        $this->assertEquals(
            ['value' => 'val'],
            (new EachConstraint([$this->createMock(IConstraint::class)]))->getErrorMessagePlaceholders('val')
        );
    }

    public function testMultipleConstraintsAreAccepted(): void
    {
        $constraint1 = $this->createMock(IConstraint::class);
        $constraint1->expects($this->once())
            ->method('passes')
            ->with('foo')
            ->willReturn(true);
        $constraint2 = $this->createMock(IConstraint::class);
        $constraint2->expects($this->once())
            ->method('passes')
            ->with('foo')
            ->willReturn(true);
        $eachConstraint = new EachConstraint([$constraint1, $constraint2], 'foo');
        $this->assertTrue($eachConstraint->passes(['foo']));
    }

    public function testPassesOnNonIterableValueThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value must be iterable');
        $eachConstraint = new EachConstraint($this->createMock(IConstraint::class), 'foo');
        $eachConstraint->passes('foo');
    }

    public function testPassesOnEmptyValueReturnsTrue(): void
    {
        $constraint = $this->createMock(IConstraint::class);
        $constraint->expects($this->never())
            ->method('passes');
        $eachConstraint = new EachConstraint($constraint, 'foo');
        $this->assertTrue($eachConstraint->passes([]));
    }

    public function testPassesOnAllPassedConstraintsReturnsTrue(): void
    {
        $constraint1 = $this->createMock(IConstraint::class);
        $constraint1->expects($this->once())
            ->method('passes')
            ->with('foo')
            ->willReturn(true);
        $constraint2 = $this->createMock(IConstraint::class);
        $constraint2->expects($this->once())
            ->method('passes')
            ->with('foo')
            ->willReturn(true);
        $eachConstraint = new EachConstraint([$constraint1, $constraint2], 'foo');
        $this->assertTrue($eachConstraint->passes(['foo']));
    }

    public function testPassesOnFailedConstraintDoesNotCallSecondConstraint(): void
    {
        $constraint1 = $this->createMock(IConstraint::class);
        $constraint1->expects($this->once())
            ->method('passes')
            ->with('foo')
            ->willReturn(false);
        $constraint2 = $this->createMock(IConstraint::class);
        $constraint2->expects($this->never())
            ->method('passes');
        $eachConstraint = new EachConstraint([$constraint1, $constraint2], 'foo');
        $this->assertFalse($eachConstraint->passes(['foo']));
    }

    public function testPassesOnPassedAndFailedConstraintsReturnsFalse(): void
    {
        $constraint1 = $this->createMock(IConstraint::class);
        $constraint1->expects($this->once())
            ->method('passes')
            ->with('foo')
            ->willReturn(true);
        $constraint2 = $this->createMock(IConstraint::class);
        $constraint2->expects($this->once())
            ->method('passes')
            ->with('foo')
            ->willReturn(false);
        $eachConstraint = new EachConstraint([$constraint1, $constraint2], 'foo');
        $this->assertFalse($eachConstraint->passes(['foo']));
    }

    public function testSingleConstraintIsAccepted(): void
    {
        $constraint = $this->createMock(IConstraint::class);
        $constraint->expects($this->once())
            ->method('passes')
            ->with('foo')
            ->willReturn(true);
        $eachConstraint = new EachConstraint($constraint, 'foo');
        $this->assertTrue($eachConstraint->passes(['foo']));
    }
}
