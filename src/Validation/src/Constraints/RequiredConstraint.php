<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints;

/**
 * Defines the required constraint
 */
class RequiredConstraint extends Constraint
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
    public function passes(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        if ($value === '') {
            return false;
        }

        if (\is_countable($value) && \count($value) === 0) {
            return false;
        }

        return true;
    }
}
