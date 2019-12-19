<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints;

use Aphiria\Validation\ValidationContext;

/**
 * Defines the interface for constraints to implement
 */
interface IConstraint
{
    /**
     * Gets the ID of the error message associated with this constraint
     * Note: If not supporting localization, this could contains the error message itself
     *
     * @return string The error message ID
     */
    public function getErrorMessageId(): string;

    /**
     * Gets whether or not the constraint passes
     *
     * @param mixed $value The value to validate
     * @param ValidationContext $validationContext The context to perform validation in
     * @return bool True if the constraint passes, otherwise false
     */
    public function passes($value, ValidationContext $validationContext): bool;
}
