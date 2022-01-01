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

use Aphiria\Validation\Constraints\RegexConstraint;
use Attribute;

/**
 * Defines the regex constraint attribute
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
final class Regex extends ConstraintAttribute
{
    /**
     * @inheritdoc
     * @param string $regex The regex to apply
     */
    public function __construct(public readonly string $regex, string $errorMessageId = null)
    {
        parent::__construct($errorMessageId);
    }

    /**
     * @inheritdoc
     */
    public function createConstraintFromAttribute(): RegexConstraint
    {
        if (isset($this->errorMessageId)) {
            return new RegexConstraint($this->regex, $this->errorMessageId);
        }

        return new RegexConstraint($this->regex);
    }
}
