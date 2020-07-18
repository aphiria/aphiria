<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints\Annotations;

use Aphiria\Validation\Constraints\RequiredConstraint;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Defines the required constraint annotation
 * @Annotation
 * @Target({"METHOD","PROPERTY"})
 */
final class Required implements IConstraintAnnotation
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
    public function createConstraintFromAnnotation(): RequiredConstraint
    {
        if (isset($this->errorMessageId)) {
            return new RequiredConstraint($this->errorMessageId);
        }

        return new RequiredConstraint();
    }
}
