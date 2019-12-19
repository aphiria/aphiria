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

/**
 * Defines the interface for object constraint registrants to implement
 */
interface IObjectConstraintRegistrant
{
    /**
     * Registers constraints from any annotations
     *
     * @param ObjectConstraintRegistry $objectConstraints The constraint registry to register to
     */
    public function registerConstraints(ObjectConstraintRegistry $objectConstraints): void;
}
