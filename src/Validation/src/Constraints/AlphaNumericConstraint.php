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
 * Defines the alpha-numeric constraint
 */
final class AlphaNumericConstraint extends ValidationConstraint
{
    /**
     * @inheritdoc
     */
    public function passes($value, ValidationContext $validationContext): bool
    {
        return ctype_alnum($value) && strpos($value, ' ') === false;
    }
}
