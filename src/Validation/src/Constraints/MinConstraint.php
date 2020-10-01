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
 * Defines the minimum constraint
 */
class MinConstraint extends Constraint
{
    /** @var string The default error message ID */
    private const DEFAULT_ERROR_MESSAGE_ID = 'Field must be more than {min}';
    /** @var int|float The minimum */
    private int|float $min;
    /** @var bool Whether or not the minimum is inclusive */
    private bool $isInclusive;

    /**
     * @inheritdoc
     * @param int|float $min The minimum
     * @param bool $isInclusive Whether or not the minimum is inclusive
     */
    public function __construct(int|float $min, bool $isInclusive, string $errorMessageId = self::DEFAULT_ERROR_MESSAGE_ID)
    {
        parent::__construct($errorMessageId);

        $this->min = $min;
        $this->isInclusive = $isInclusive;
    }

    /**
     * @inheritdoc
     */
    public function getErrorMessagePlaceholders($value): array
    {
        return \array_merge(parent::getErrorMessagePlaceholders($value), ['min' => $this->min]);
    }

    /**
     * @inheritdoc
     */
    public function passes($value): bool
    {
        if ($this->isInclusive) {
            return $value >= $this->min;
        }

        return $value > $this->min;
    }
}
