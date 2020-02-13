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

use Aphiria\Validation\Constraints\MaxConstraint;
use Doctrine\Annotations\Annotation\Target;
use InvalidArgumentException;

/**
 * Defines the max constraint annotation
 * @Annotation
 * @Target({"METHOD","PROPERTY"})
 */
final class Max implements IConstraintAnnotation
{
    /** @var int|float The maximum */
    public $max;
    /** @var bool Whether or not the maximum is inclusive */
    public bool $isInclusive;
    /** @var string|null The error message ID */
    public ?string $errorMessageId;

    /**
     * @param array $values The mapping of value names to values
     */
    public function __construct(array $values)
    {
        if (isset($values['value'])) {
            $max = $values['value'];
        } elseif (isset($values['max'])) {
            $max = $values['max'];
        } else {
            throw new InvalidArgumentException('Max must be set');
        }

        if (!\is_numeric($max)) {
            throw new InvalidArgumentException('Max must be numeric');
        }

        $this->max = $max;
        $this->isInclusive = isset($values['isInclusive']) ? (bool)$values['isInclusive'] : true;
        $this->errorMessageId = $values['errorMessageId'] ?? null;
    }

    /**
     * @inheridoc
     */
    public function createConstraintFromAnnotation(): MaxConstraint
    {
        if (isset($this->errorMessageId)) {
            return new MaxConstraint($this->max, $this->isInclusive, $this->errorMessageId);
        }

        return new MaxConstraint($this->max, $this->isInclusive);
    }
}
