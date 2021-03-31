<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints\Attributes;

use Aphiria\Validation\Constraints\NotInConstraint;
use Attribute;

/**
 * Defines the not-in constraint attribute
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
final class NotIn extends ConstraintAttribute
{
    /**
     * @inheritdoc
     * @param list<mixed> $values The values to check
     */
    public function __construct(public array $values, string $errorMessageId = null)
    {
        parent::__construct($errorMessageId);
    }

    /**
     * @inheritdoc
     */
    public function createConstraintFromAttribute(): NotInConstraint
    {
        if (isset($this->errorMessageId)) {
            return new NotInConstraint($this->values, $this->errorMessageId);
        }

        return new NotInConstraint($this->values);
    }
}
