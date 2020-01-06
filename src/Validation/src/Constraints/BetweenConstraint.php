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
use InvalidArgumentException;

/**
 * Defines the between constraint
 */
final class BetweenConstraint extends Constraint
{
    /** @var string The default error message ID */
    private const DEFAULT_ERROR_MESSAGE_ID = 'Field must be between {min} and {max}';
    /** @var int|float The minimum */
    private $min;
    /** @var int|float The maximum */
    private $max;
    /** @var bool Whether or not the min is inclusive */
    private bool $minIsInclusive;
    /** @var bool Whether or not the max is inclusive */
    private bool $maxIsInclusive;

    /**
     * @inheritdoc
     * @param int|float $min The minimum
     * @param int|float $max The maximum
     * @param bool $minIsInclusive Whether or not the min is inclusive
     * @param bool $maxIsInclusive Whether or not the max is inclusive
     * @throws InvalidArgumentException Thrown if the min or max are not numeric
     */
    public function __construct(
        $min,
        $max,
        bool $minIsInclusive,
        bool $maxIsInclusive,
        string $errorMessageId = self::DEFAULT_ERROR_MESSAGE_ID
    ) {
        parent::__construct($errorMessageId);

        if (!\is_numeric($min) || !\is_numeric($max)) {
            throw new InvalidArgumentException('Min and max values must be numeric');
        }

        $this->min = $min;
        $this->max = $max;
        $this->minIsInclusive = $minIsInclusive;
        $this->maxIsInclusive = $maxIsInclusive;
    }

    /**
     * @inheritdoc
     */
    public function getErrorMessagePlaceholders($value): array
    {
        return \array_merge(parent::getErrorMessagePlaceholders($value), ['min' => $this->min, 'max' => $this->max]);
    }

    /**
     * @inheritdoc
     */
    public function passes($value, ValidationContext $validationContext): bool
    {
        if (!\is_numeric($value)) {
            throw new InvalidArgumentException('Value must be numeric');
        }

        $passesMin = $this->minIsInclusive ? $value >= $this->min : $value > $this->min;
        $passesMax = $this->maxIsInclusive ? $value <= $this->max : $value < $this->max;

        return $passesMin && $passesMax;
    }
}
