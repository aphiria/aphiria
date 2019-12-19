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

use Aphiria\Validation\Constraints\IntegerConstraint;
use Doctrine\Annotations\Annotation\Target;

/**
 * Defines the integer constraint annotation
 * @Annotation
 * @Target({"METHOD","PROPERTY"})
 */
final class Integer implements IConstraintAnnotation
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
    public function createConstraintFromAnnotation(): IntegerConstraint
    {
        if (isset($this->errorMessageId)) {
            return new IntegerConstraint($this->errorMessageId);
        }

        return new IntegerConstraint();
    }
}
