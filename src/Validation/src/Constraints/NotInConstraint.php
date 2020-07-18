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
 * Defines the not-in-array constraint
 */
class NotInConstraint extends Constraint
{
    /** @var string The default error message ID */
    private const DEFAULT_ERROR_MESSAGE_ID = 'Field is invalid';
    /** @var array The values to check */
    private array $values;

    /**
     * @inheritdoc
     * @param array $values The values to check
     */
    public function __construct(array $values, string $errorMessageId = self::DEFAULT_ERROR_MESSAGE_ID)
    {
        parent::__construct($errorMessageId);

        $this->values = $values;
    }

    /**
     * @inheritdoc
     */
    public function passes($value): bool
    {
        return !\in_array($value, $this->values);
    }
}
