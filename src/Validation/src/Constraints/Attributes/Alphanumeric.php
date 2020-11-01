<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints\Attributes;

use Aphiria\Validation\Constraints\AlphanumericConstraint;
use Attribute;

/**
 * Defines the alphanumeric constraint attribute
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
final class Alphanumeric extends ConstraintAttribute
{
    /**
     * @inheritdoc
     */
    public function __construct(string $errorMessageId = null)
    {
        parent::__construct($errorMessageId);
    }

    /**
     * @inheridoc
     */
    public function createConstraintFromAttribute(): AlphanumericConstraint
    {
        if (isset($this->errorMessageId)) {
            return new AlphanumericConstraint($this->errorMessageId);
        }

        return new AlphanumericConstraint();
    }
}
