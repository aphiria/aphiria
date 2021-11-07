<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation;

use Aphiria\Validation\Constraints\IConstraint;

/**
 * Defines a constraint violation
 */
final class ConstraintViolation
{
    /**
     * @param string $errorMessage The error message
     * @param IConstraint $constraint The constraint that was violated
     * @param mixed $invalidValue The invalid value
     * @param mixed $rootValue The root value that was being validated
     * @param string|null $propertyName The name of the property that was being validated
     * @param string|null $methodName The name of the method that was being validated
     */
    public function __construct(
        public readonly string $errorMessage,
        public readonly IConstraint $constraint,
        public readonly mixed $invalidValue,
        public readonly mixed $rootValue,
        public readonly ?string $propertyName = null,
        public readonly ?string $methodName = null
    ) {
    }
}
