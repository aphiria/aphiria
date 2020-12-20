<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints\Attributes;

use Aphiria\Validation\Constraints\BetweenConstraint;
use Attribute;

/**
 * Defines the between constraint attribute
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
final class Between extends ConstraintAttribute
{
    /**
     * @inheritdoc
     * @param int|float $min The minimum
     * @param int|float $max The maximum
     * @param bool $minIsInclusive Whether or not the min is inclusive
     * @param bool $maxIsInclusive Whether or not the max is inclusive
     */
    public function __construct(
        public int|float $min,
        public int|float $max,
        public bool $minIsInclusive = true,
        public bool $maxIsInclusive = true,
        string $errorMessageId = null
    ) {
        parent::__construct($errorMessageId);
    }

    /**
     * @inheridoc
     */
    public function createConstraintFromAttribute(): BetweenConstraint
    {
        if (isset($this->errorMessageId)) {
            return new BetweenConstraint(
                $this->min,
                $this->max,
                $this->minIsInclusive,
                $this->maxIsInclusive,
                $this->errorMessageId
            );
        }

        return new BetweenConstraint($this->min, $this->max, $this->minIsInclusive, $this->maxIsInclusive);
    }
}
