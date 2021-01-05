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

use Aphiria\Validation\Constraints\EqualsConstraint;
use Attribute;

/**
 * Defines the equals constraint attribute
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
final class Equals extends ConstraintAttribute
{
    /**
     * @inheritdoc
     * @param mixed $value The value to compare against
     */
    public function __construct(public mixed $value, string $errorMessageId = null)
    {
        parent::__construct($errorMessageId);
    }

    /**
     * @inheritdoc
     */
    public function createConstraintFromAttribute(): EqualsConstraint
    {
        if (isset($this->errorMessageId)) {
            return new EqualsConstraint($this->value, $this->errorMessageId);
        }

        return new EqualsConstraint($this->value);
    }
}
