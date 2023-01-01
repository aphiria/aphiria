<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation;

use Aphiria\Validation\Constraints\IConstraint;

/**
 * Defines a constraint violation
 */
final readonly class ConstraintViolation
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
        public string $errorMessage,
        public IConstraint $constraint,
        public mixed $invalidValue,
        public mixed $rootValue,
        public ?string $propertyName = null,
        public ?string $methodName = null
    ) {
    }
}
