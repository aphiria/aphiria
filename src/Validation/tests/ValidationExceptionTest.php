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
use Aphiria\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class ValidationExceptionTest extends TestCase
{
    public function testViolationsAreSameOnesPassedViaConstructor(): void
    {
        $violations = [new ConstraintViolation('foo', $this->createMock(IConstraint::class), 'bar', 'baz')];
        $exception = new ValidationException($violations);
        $this->assertSame($violations, $exception->violations);
    }
}
