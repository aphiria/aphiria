<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation;

/**
 * Defines the interface for constraint registrants to implement
 */
interface IConstraintRegistrant
{
    /**
     * Registers constraints from any annotations
     *
     * @param ConstraintRegistry $constraints The constraint registry to register to
     */
    public function registerConstraints(ConstraintRegistry $constraints): void;
}
