<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ValidationAnnotations\Annotations;

use Aphiria\Validation\Constraints\AlphaConstraint;
use Doctrine\Annotations\Annotation\Target;

/**
 * Defines the alpha constraint annotation
 * @Annotation
 * @Target({"METHOD","PROPERTY"})
 */
final class Alpha implements IValidationConstraintAnnotation
{
    /** @var string The error message ID */
    public string $errorMessageId;

    /**
     * @param array $values The mapping of value names to values
     */
    public function __construct(array $values)
    {
        // TODO: For this and all other constraints, determine what to set the error message ID to
        $this->errorMessageId = $values['errorMessageId'] ?? '';
    }

    /**
     * @inheridoc
     */
    public function createConstraintFromAnnotation(): AlphaConstraint
    {
        return new AlphaConstraint($this->errorMessageId);
    }
}
