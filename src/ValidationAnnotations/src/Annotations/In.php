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

use Aphiria\Validation\Constraints\InConstraint;
use Doctrine\Annotations\Annotation\Target;
use InvalidArgumentException;

/**
 * Defines the in constraint annotation
 * @Annotation
 * @Target({"METHOD","PROPERTY"})
 */
final class In implements IValidationConstraintAnnotation
{
    /** @var array The values to check */
    public array $values;
    /** @var string The error message ID */
    public string $errorMessageId;

    /**
     * @param array $values The mapping of value names to values
     */
    public function __construct(array $values)
    {
        if (!isset($values['value']) || !\is_array($values['value'])) {
            throw new InvalidArgumentException('Value must be set to an array');
        }

        $this->values = $values['value'];
        $this->errorMessageId = $values['errorMessageId'] ?? '';
    }

    /**
     * @inheridoc
     */
    public function createConstraintFromAnnotation(): InConstraint
    {
        return new InConstraint($this->values, $this->errorMessageId);
    }
}
