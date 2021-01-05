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

use Aphiria\Validation\Constraints\IConstraint;

/**
 * Defines the interface that all validation constraint attributes must validate
 * Note: This interface simplifies our search for validation constraints by having a common parent type
 */
interface IConstraintAttribute
{
    /**
     * Creates a constraint from the attribute
     *
     * @return IConstraint The created constraint
     */
    public function createConstraintFromAttribute(): IConstraint;
}
