<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints;

/**
 * Defines the interface for constraints to implement
 */
interface IConstraint
{
    /**
     * @var string The error message ID
     * @note If not supporting localization, this could contains the error message itself
     */
    public string $errorMessageId { get; }

    /**
     * Gets the values that can be used to compile error messages
     *
     * @param mixed $value The value that was being validated
     * @return array<string, string|int|float> The mapping of placeholder names to values
     */
    public function getErrorMessagePlaceholders(mixed $value): array;

    /**
     * Gets whether or not the constraint passes
     *
     * @param mixed $value The value to validate
     * @return bool True if the constraint passes, otherwise false
     */
    public function passes(mixed $value): bool;
}
