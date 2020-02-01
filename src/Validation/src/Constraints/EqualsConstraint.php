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
 * Defines the equals constraint
 */
final class EqualsConstraint extends Constraint
{
    /** @var string The default error message ID */
    private const DEFAULT_ERROR_MESSAGE_ID = 'Field does not match expected value';
    /** @var mixed The value to compare against */
    private $value;

    /**
     * @inheritdoc
     * @param mixed $value The value to compare against
     */
    public function __construct($value, string $errorMessageId = self::DEFAULT_ERROR_MESSAGE_ID)
    {
        parent::__construct($errorMessageId);

        $this->value = $value;
    }

    /**
     * @inheritdoc
     */
    public function passes($value): bool
    {
        return $value === $this->value;
    }
}
