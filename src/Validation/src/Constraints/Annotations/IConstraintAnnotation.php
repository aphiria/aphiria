<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints\Annotations;

use Aphiria\Validation\Constraints\IConstraint;

/**
 * Defines the interface that all validation constraint annotations must validate
 * Note: This interface simplifies our search for validation constraints by having a common parent type
 */
interface IConstraintAnnotation
{
    /**
     * Creates a constraint from the annotation
     *
     * @return IConstraint The created constraint
     */
    public function createConstraintFromAnnotation(): IConstraint;
}
