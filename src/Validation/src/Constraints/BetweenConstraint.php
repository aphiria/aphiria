<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints;

use InvalidArgumentException;

/**
 * Defines the between constraint
 */
final class BetweenConstraint extends Constraint
{
    /** @var string The default error message ID */
    private const DEFAULT_ERROR_MESSAGE_ID = 'Field must be between {min} and {max}';
    /**
     * @inheritdoc
     * @param int|float $min The minimum
     * @param int|float $max The maximum
     * @param bool $minIsInclusive Whether or not the min is inclusive
     * @param bool $maxIsInclusive Whether or not the max is inclusive
     */
    public function __construct(
        private readonly int|float $min,
        private readonly int|float $max,
        private readonly bool $minIsInclusive,
        private readonly bool $maxIsInclusive,
        string $errorMessageId = self::DEFAULT_ERROR_MESSAGE_ID
    ) {
        parent::__construct($errorMessageId);
    }

    /**
     * @inheritdoc
     */
    public function getErrorMessagePlaceholders($value): array
    {
        return [...parent::getErrorMessagePlaceholders($value), ...['min' => $this->min, 'max' => $this->max]];
    }

    /**
     * @inheritdoc
     */
    public function passes($value): bool
    {
        if (!\is_numeric($value)) {
            throw new InvalidArgumentException('Value must be numeric');
        }

        $passesMin = $this->minIsInclusive ? $value >= $this->min : $value > $this->min;
        $passesMax = $this->maxIsInclusive ? $value <= $this->max : $value < $this->max;

        return $passesMin && $passesMax;
    }
}
