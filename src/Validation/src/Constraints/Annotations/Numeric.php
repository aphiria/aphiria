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

use Aphiria\Validation\Constraints\NumericConstraint;
use Doctrine\Annotations\Annotation\Target;

/**
 * Defines the numeric constraint annotation
 * @Annotation
 * @Target({"METHOD","PROPERTY"})
 */
final class Numeric implements IValidationConstraintAnnotation
{
    /** @var string|null The error message ID */
    public ?string $errorMessageId;

    /**
     * @param array $values The mapping of value names to values
     */
    public function __construct(array $values)
    {
        $this->errorMessageId = $values['errorMessageId'] ?? null;
    }

    /**
     * @inheridoc
     */
    public function createConstraintFromAnnotation(): NumericConstraint
    {
        if (isset($this->errorMessageId)) {
            return new NumericConstraint($this->errorMessageId);
        }

        return new NumericConstraint();
    }
}
