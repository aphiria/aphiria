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

use Aphiria\Validation\Constraints\RequiredConstraint;
use Attribute;

/**
 * Defines the required constraint attribute
 */
#[Attribute(Attribute::TARGET_METHOD|Attribute::TARGET_PROPERTY)]
final class Required extends ConstraintAttribute
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
    public function createConstraintFromAttribute(): RequiredConstraint
    {
        if (isset($this->errorMessageId)) {
            return new RequiredConstraint($this->errorMessageId);
        }

        return new RequiredConstraint();
    }
}
