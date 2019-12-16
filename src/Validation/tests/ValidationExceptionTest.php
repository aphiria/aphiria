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

use Aphiria\Validation\ValidationContext;
use Aphiria\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the validation exception
 */
class ValidationExceptionTest extends TestCase
{
    public function testErrorsAreSameOnesPassedViaConstructor(): void
    {
        $context = new ValidationContext($this);
        $exception = new ValidationException($context);
        $this->assertSame($context, $exception->getValidationContext());
    }
}
