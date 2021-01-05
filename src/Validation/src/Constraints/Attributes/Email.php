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

use Aphiria\Validation\Constraints\EmailConstraint;
use Attribute;

/**
 * Defines the email constraint attribute
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
final class Email extends ConstraintAttribute
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
    public function createConstraintFromAttribute(): EmailConstraint
    {
        if (isset($this->errorMessageId)) {
            return new EmailConstraint($this->errorMessageId);
        }

        return new EmailConstraint();
    }
}
