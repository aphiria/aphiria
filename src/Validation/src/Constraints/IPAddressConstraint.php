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
 * Defines the IP address constraint
 */
class IPAddressConstraint extends ValidationConstraint
{
    /**
     * @inheritdoc
     */
    public function passes($value, ValidationContext $validationContext): bool
    {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }
}
