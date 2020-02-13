<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests;

use Aphiria\Validation\Constraints\IConstraint;
use Aphiria\Validation\ConstraintViolation;
use Aphiria\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the validation exception
 */
class ValidationExceptionTest extends TestCase
{
    public function testViolationsAreSameOnesPassedViaConstructor(): void
    {
        $violations = [new ConstraintViolation('foo', $this->createMock(IConstraint::class), 'bar', 'baz')];
        $exception = new ValidationException($violations);
        $this->assertSame($violations, $exception->getViolations());
    }
}
