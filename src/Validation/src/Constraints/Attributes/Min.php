<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints\Attributes;

use Aphiria\Validation\Constraints\MinConstraint;
use Attribute;

/**
 * Defines the min constraint attribute
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
final class Min extends ConstraintAttribute
{
    /**
     * @inheritdoc
     * @param int|float $min The minimum
     * @param bool $isInclusive Whether or not the minimum is inclusive
     */
    public function __construct(public int|float $min, public bool $isInclusive = true, string $errorMessageId = null)
    {
        parent::__construct($errorMessageId);
    }

    /**
     * @inheritdoc
     */
    public function createConstraintFromAttribute(): MinConstraint
    {
        if (isset($this->errorMessageId)) {
            return new MinConstraint($this->min, $this->isInclusive, $this->errorMessageId);
        }

        return new MinConstraint($this->min, $this->isInclusive);
    }
}
