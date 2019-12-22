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
    /** @var bool Whether or not the min is inclusive */
    private bool $minIsInclusive;
    /** @var bool Whether or not the max is inclusive */
    private bool $maxIsInclusive;

    /**
     * @param int|float $min The min value
     * @param int|float $max The max value
     * @param bool $minIsInclusive Whether or not the min is inclusive
     * @param bool $maxIsInclusive Whether or not the min is inclusive
     * @throws InvalidArgumentException Thrown if the min or max values are invalid
     */
    public function __construct($min, $max, bool $minIsInclusive = true, bool $maxIsInclusive = true)
    {
        if (!\is_numeric($min)) {
            throw new InvalidArgumentException('Min value must be numeric');
        }

        if (!\is_numeric($max)) {
            throw new InvalidArgumentException('Max value must be numeric');
        }

        $this->min = $min;
        $this->max = $max;
        $this->minIsInclusive = $minIsInclusive;
        $this->maxIsInclusive = $maxIsInclusive;
    }

    /**
     * Gets the slug that will be used to actually add a constraint in a URI template
     *
     * @return string The slug used in the URI template
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
        if (!\is_numeric($value)) {
            throw new InvalidArgumentException('Value must be numeric');
        }

        $passesMin = $this->minIsInclusive ? $value >= $this->min : $value > $this->min;
        $passesMax = $this->maxIsInclusive ? $value <= $this->max : $value < $this->max;

        return $passesMin && $passesMax;
    }
}
