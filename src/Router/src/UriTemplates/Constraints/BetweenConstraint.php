<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Constraints;

use InvalidArgumentException;

/**
 * Defines the between constraint
 */
final class BetweenConstraint implements IRouteVariableConstraint
{
    /**
     * @param int|float $min The min value
     * @param int|float $max The max value
     * @param bool $minIsInclusive Whether or not the min is inclusive
     * @param bool $maxIsInclusive Whether or not the min is inclusive
     */
    public function __construct(
        private readonly int|float $min,
        private readonly int|float $max,
        private readonly bool $minIsInclusive = true,
        private readonly bool $maxIsInclusive = true
    ) {
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
    public function passes(mixed $value): bool
    {
        if (!\is_numeric($value)) {
            throw new InvalidArgumentException('Value must be numeric');
        }

        $passesMin = $this->minIsInclusive ? $value >= $this->min : $value > $this->min;
        $passesMax = $this->maxIsInclusive ? $value <= $this->max : $value < $this->max;

        return $passesMin && $passesMax;
    }
}
