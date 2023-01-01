<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests;

use Aphiria\Validation\Constraints\IConstraint;
use Aphiria\Validation\ConstraintViolation;
use PHPUnit\Framework\TestCase;

class ConstraintViolationTest extends TestCase
{
    public function testGettingErrorMessageReturnsOneSetInConstructor(): void
    {
        $violation = new ConstraintViolation('error', $this->createMock(IConstraint::class), 'foo', 'bar');
        $this->assertSame('error', $violation->errorMessage);
    }

    public function testGetInvalidValueReturnsOneSetInConstructor(): void
    {
        $violation = new ConstraintViolation(
            'error',
            $this->createMock(IConstraint::class),
            'foo',
            'bar'
        );
        $this->assertSame('foo', $violation->invalidValue);
    }

    public function testGetMethodNameReturnsOneSetInConstructor(): void
    {
        $violation = new ConstraintViolation(
            'error',
            $this->createMock(IConstraint::class),
            'foo',
            'bar',
            null,
            'method'
        );
        $this->assertSame('method', $violation->methodName);
    }

    public function testGetPropertyNameReturnsOneSetInConstructor(): void
    {
        $violation = new ConstraintViolation(
            'error',
            $this->createMock(IConstraint::class),
            'foo',
            'bar',
            'prop'
        );
        $this->assertSame('prop', $violation->propertyName);
    }

    public function testGetRootValueReturnsOneSetInConstructor(): void
    {
        $violation = new ConstraintViolation(
            'error',
            $this->createMock(IConstraint::class),
            'foo',
            'bar'
        );
        $this->assertSame('bar', $violation->rootValue);
    }

    public function testGetConstraintReturnsOneSetInConstructor(): void
    {
        $expectedConstraint = $this->createMock(IConstraint::class);
        $violation = new ConstraintViolation(
            'error',
            $expectedConstraint,
            'foo',
            'bar'
        );
        $this->assertSame($expectedConstraint, $violation->constraint);
    }
}
