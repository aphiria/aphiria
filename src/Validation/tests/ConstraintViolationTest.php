<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests;

use Aphiria\Validation\Constraints\IValidationConstraint;
use Aphiria\Validation\ConstraintViolation;
use PHPUnit\Framework\TestCase;

/**
 * Tests the constraint violation
 */
class ConstraintViolationTest extends TestCase
{
    public function testGetInvalidValueReturnsOneSetInConstructor(): void
    {
        $violation = new ConstraintViolation(
            $this->createMock(IValidationConstraint::class),
            'foo',
            'bar'
        );
        $this->assertEquals('foo', $violation->getInvalidValue());
    }

    public function testGetMethodNameReturnsOneSetInConstructor(): void
    {
        $violation = new ConstraintViolation(
            $this->createMock(IValidationConstraint::class),
            'foo',
            'bar',
            null,
            'method'
        );
        $this->assertEquals('method', $violation->getMethodName());
    }

    public function testGetPropertyNameReturnsOneSetInConstructor(): void
    {
        $violation = new ConstraintViolation(
            $this->createMock(IValidationConstraint::class),
            'foo',
            'bar',
            'prop'
        );
        $this->assertEquals('prop', $violation->getPropertyName());
    }

    public function testGetRootValueReturnsOneSetInConstructor(): void
    {
        $violation = new ConstraintViolation(
            $this->createMock(IValidationConstraint::class),
            'foo',
            'bar'
        );
        $this->assertEquals('bar', $violation->getRootValue());
    }

    public function testGetConstraintReturnsOneSetInConstructor(): void
    {
        $expectedConstraint = $this->createMock(IValidationConstraint::class);
        $violation = new ConstraintViolation(
            $expectedConstraint,
            'foo',
            'bar'
        );
        $this->assertSame($expectedConstraint, $violation->getConstraint());
    }
}
