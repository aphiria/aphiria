<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Rules;

use Aphiria\Validation\ValidationContext;
use LogicException;

/**
 * Defines the interface for rules to implement
 */
interface IRule
{
    /**
     * Gets the ID of the error message associated with this rule
     * Note: If not supporting localization, this could contains the error message itself
     *
     * @return string The error message ID
     */
    public function getErrorMessageId(): string;

    /**
     * Gets whether or not the rule passes
     *
     * @param mixed $value The value to validate
     * @param ValidationContext $validationContext The context to perform validation in
     * @return bool True if the rule passes, otherwise false
     * @throws LogicException Thrown if the rule was not set up correctly
     */
    public function passes($value, ValidationContext $validationContext): bool;
}
