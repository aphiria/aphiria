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
use Countable;

/**
 * Defines the required constraint
 */
class RequiredConstraint extends ValidationConstraint
{
    /** @var string The default error message ID */
    private const DEFAULT_ERROR_MESSAGE_ID = 'Field is required';

    /**
     * @param string $errorMessageId The ID of the error message associated with this constraint
     */
    public function __construct(string $errorMessageId = self::DEFAULT_ERROR_MESSAGE_ID)
    {
        parent::__construct($errorMessageId);
    }

    /**
     * @inheritdoc
     */
    public function passes($value, ValidationContext $validationContext): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_string($value) && $value === '') {
            return false;
        }

        if ((is_array($value) || $value instanceof Countable) && count($value) === 0) {
            return false;
        }

        return true;
    }
}
