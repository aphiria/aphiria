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

use Attribute;

/**
 * Defines the base class for constraint attributes to implement
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
abstract class ConstraintAttribute implements IConstraintAttribute
{
    /**
     * @param string|null $errorMessageId The error message ID, or null if there is none
     */
    protected function __construct(public ?string $errorMessageId)
    {
    }
}
