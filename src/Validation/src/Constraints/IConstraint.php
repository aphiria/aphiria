<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints;

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
     * Gets the values that can be used to compile error messages
     *
     * @param mixed $value The value that was being validated
     * @return array The mapping of placeholder names to values
     */
    public function getErrorMessagePlaceholders($value): array;

    /**
     * Gets whether or not the constraint passes
     *
     * @param mixed $value The value to validate
     * @return bool True if the constraint passes, otherwise false
     */
    public function passes($value): bool;
}
