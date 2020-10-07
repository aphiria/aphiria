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

use Aphiria\Validation\Constraints\AlphaConstraint;
use Attribute;

/**
 * Defines the alpha constraint attribute
 */
#[Attribute(Attribute::TARGET_METHOD|Attribute::TARGET_PROPERTY)]
final class Alpha extends ConstraintAttribute
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
    public function createConstraintFromAttribute(): AlphaConstraint
    {
        if (isset($this->errorMessageId)) {
            return new AlphaConstraint($this->errorMessageId);
        }

        return new AlphaConstraint();
    }
}
