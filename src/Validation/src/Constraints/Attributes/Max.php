<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints\Attributes;

use Aphiria\Validation\Constraints\MaxConstraint;
use Attribute;

/**
 * Defines the max constraint attribute
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
final class Max extends ConstraintAttribute
{
    /**
     * @inheritdoc
     * @param int|float $max The maximum
     * @param bool $isInclusive Whether or not the maximum is inclusive
     */
    public function __construct(public int|float $max, public bool $isInclusive = true, string $errorMessageId = null)
    {
        parent::__construct($errorMessageId);
    }

    /**
     * @inheritdoc
     */
    public function createConstraintFromAttribute(): MaxConstraint
    {
        if (isset($this->errorMessageId)) {
            return new MaxConstraint($this->max, $this->isInclusive, $this->errorMessageId);
        }

        return new MaxConstraint($this->max, $this->isInclusive);
    }
}
