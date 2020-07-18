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

use Aphiria\Validation\Constraints\NotInConstraint;
use Doctrine\Common\Annotations\Annotation\Target;
use InvalidArgumentException;

/**
 * Defines the not-in constraint annotation
 * @Annotation
 * @Target({"METHOD","PROPERTY"})
 */
final class NotIn implements IConstraintAnnotation
{
    /** @var array The values to check */
    public array $values;
    /** @var string|null The error message ID */
    public ?string $errorMessageId;

    /**
     * @param array $values The mapping of value names to values
     */
    public function __construct(array $values)
    {
        if (!isset($values['value']) || !\is_array($values['value'])) {
            throw new InvalidArgumentException('Value must be set to an array');
        }

        $this->values = $values['value'];
        $this->errorMessageId = $values['errorMessageId'] ?? null;
    }

    /**
     * @inheridoc
     */
    public function createConstraintFromAnnotation(): NotInConstraint
    {
        if (isset($this->errorMessageId)) {
            return new NotInConstraint($this->values, $this->errorMessageId);
        }

        return new NotInConstraint($this->values);
    }
}
