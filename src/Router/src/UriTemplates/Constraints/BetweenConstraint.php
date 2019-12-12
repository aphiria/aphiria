<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Constraints;

use InvalidArgumentException;

/**
 * Defines the between constraint
 */
final class BetweenConstraint implements IRouteVariableConstraint
{
    /** @var int|float The min value */
    private $min;
    /** @var int|float The max value */
    private $max;
    /** @var bool Whether or not the extremes are inclusive */
    private bool $isInclusive;

    /**
     * @param int|float $min The min value
     * @param int|float $max The max value
     * @param bool $isInclusive Whether or not the extremes are inclusive
     * @throws InvalidArgumentException Thrown if the min or max values are invalid
     */
    public function __construct($min, $max, bool $isInclusive = true)
    {
        if (!\is_numeric($min)) {
            throw new InvalidArgumentException('Min value must be numeric');
        }

        if (!\is_numeric($max)) {
            throw new InvalidArgumentException('Max value must be numeric');
        }

        $this->min = $min;
        $this->max = $max;
        $this->isInclusive = $isInclusive;
    }

    /**
     * @inheritdoc
     */
    public static function getSlug(): string
    {
        return 'between';
    }

    /**
     * @inheritdoc
     */
    public function passes($value): bool
    {
        if ($this->isInclusive) {
            return $value >= $this->min && $value <= $this->max;
        }

        return $value > $this->min && $value < $this->max;
    }
}
