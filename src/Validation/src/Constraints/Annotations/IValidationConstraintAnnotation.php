<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints\Annotations;

use Aphiria\Validation\Constraints\IValidationConstraint;

/**
 * Defines the interface that all validation constraint annotations must validate
 * Note: This interface simplifies our search for validation constraints by having a common parent type
 */
interface IValidationConstraintAnnotation
{
    /**
     * Creates a constraint from the annotation
     *
     * @return IValidationConstraint The created constraint
     */
    public function createConstraintFromAnnotation(): IValidationConstraint;
}
