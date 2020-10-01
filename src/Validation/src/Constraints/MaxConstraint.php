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
 * Defines the maximum constraint
 */
class MaxConstraint extends Constraint
{
    /** @var string The default error message ID */
    private const DEFAULT_ERROR_MESSAGE_ID = 'Field must be less than {max}';
    /** @var int|float The maximum */
    private int|float $max;
    /** @var bool Whether or not the maximum is inclusive */
    private bool $isInclusive;

    /**
     * @inheritdoc
     * @param int|float $max The maximum
     * @param bool $isInclusive Whether or not the maximum is inclusive
     */
    public function __construct(int|float $max, bool $isInclusive, string $errorMessageId = self::DEFAULT_ERROR_MESSAGE_ID)
    {
        parent::__construct($errorMessageId);

        $this->max = $max;
        $this->isInclusive = $isInclusive;
    }

    /**
     * @inheritdoc
     */
    public function getErrorMessagePlaceholders($value): array
    {
        return \array_merge(parent::getErrorMessagePlaceholders($value), ['max' => $this->max]);
    }

    /**
     * @inheritdoc
     */
    public function passes($value): bool
    {
        if ($this->isInclusive) {
            return $value <= $this->max;
        }

        return $value < $this->max;
    }
}
