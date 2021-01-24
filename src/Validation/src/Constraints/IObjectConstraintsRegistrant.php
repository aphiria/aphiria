<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints;

/**
 * Defines the interface for object constraint registrants to implement
 */
interface IObjectConstraintsRegistrant
{
    /**
     * Registers validation constraints
     *
     * @param ObjectConstraintsRegistry $objectConstraints The constraint registry to register to
     */
    public function registerConstraints(ObjectConstraintsRegistry $objectConstraints): void;
}
