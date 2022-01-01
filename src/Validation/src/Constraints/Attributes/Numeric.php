<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints\Attributes;

use Aphiria\Validation\Constraints\NumericConstraint;
use Attribute;

/**
 * Defines the numeric constraint attribute
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
final class Numeric extends ConstraintAttribute
{
    /**
     * @inheritdoc
     */
    public function __construct(string $errorMessageId = null)
    {
        parent::__construct($errorMessageId);
    }

    /**
     * @inheritdoc
     */
    public function createConstraintFromAttribute(): NumericConstraint
    {
        if (isset($this->errorMessageId)) {
            return new NumericConstraint($this->errorMessageId);
        }

        return new NumericConstraint();
    }
}
