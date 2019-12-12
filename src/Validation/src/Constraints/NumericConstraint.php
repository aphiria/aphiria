<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints;

use Aphiria\Validation\ValidationContext;

/**
 * Defines the numeric constraint
 */
class NumericConstraint extends ValidationConstraint
{
    /**
     * @inheritdoc
     */
    public function passes($value, ValidationContext $validationContext): bool
    {
        return is_numeric($value);
    }
}
