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

/**
 * Defines the numeric constraint
 */
class NumericConstraint extends Constraint
{
    /** @var string The default error message ID */
    private const DEFAULT_ERROR_MESSAGE_ID = 'Field must be numeric';

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
    public function passes($value): bool
    {
        return is_numeric($value);
    }
}
