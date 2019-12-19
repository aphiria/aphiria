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

use Aphiria\Validation\Constraints\MinConstraint;
use Doctrine\Annotations\Annotation\Target;
use InvalidArgumentException;

/**
 * Defines the min constraint annotation
 * @Annotation
 * @Target({"METHOD","PROPERTY"})
 */
final class Min implements IConstraintAnnotation
{
    /** @var int|float The minimum */
    public $min;
    /** @var bool Whether or not the minimum is inclusive */
    public bool $isInclusive;
    /** @var string|null The error message ID */
    public ?string $errorMessageId;

    /**
     * @param array $values The mapping of value names to values
     */
    public function __construct(array $values)
    {
        if (isset($values['value'])) {
            $min = $values['value'];
        } elseif (isset($values['min'])) {
            $min = $values['min'];
        } else {
            throw new InvalidArgumentException('Min must be set');
        }

        if (!\is_numeric($min)) {
            throw new InvalidArgumentException('Min must be numeric');
        }

        $this->min = $min;
        $this->isInclusive = isset($values['isInclusive']) ? (bool)$values['isInclusive'] : true;
        $this->errorMessageId = $values['errorMessageId'] ?? null;
    }

    /**
     * @inheridoc
     */
    public function createConstraintFromAnnotation(): MinConstraint
    {
        if (isset($this->errorMessageId)) {
            return new MinConstraint($this->min, $this->isInclusive, $this->errorMessageId);
        }

        return new MinConstraint($this->min, $this->isInclusive);
    }
}
