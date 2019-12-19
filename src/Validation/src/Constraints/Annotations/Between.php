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

use Aphiria\Validation\Constraints\BetweenConstraint;
use Doctrine\Annotations\Annotation\Target;
use InvalidArgumentException;

/**
 * Defines the between constraint annotation
 * @Annotation
 * @Target({"METHOD","PROPERTY"})
 */
final class Between implements IConstraintAnnotation
{
    /** @var int|float The minimum */
    public $min;
    /** @var int|float The maximum */
    public $max;
    /** @var bool Whether or not the extremes are inclusive */
    public bool $isInclusive;
    /** @var string|null The error message ID */
    public ?string $errorMessageId;

    /**
     * @param array $values The mapping of value names to values
     */
    public function __construct(array $values)
    {
        if (!isset($values['min']) || !\is_numeric($values['min'])) {
            throw new InvalidArgumentException('Must specify a numeric min value');
        }

        if (!isset($values['max']) || !\is_numeric($values['max'])) {
            throw new InvalidArgumentException('Must specify a numeric max value');
        }

        $this->min = $values['min'];
        $this->max = $values['max'];
        $this->isInclusive = isset($values['isInclusive']) ? (bool)$values['isInclusive'] : true;
        $this->errorMessageId = $values['errorMessageId'] ?? null;
    }

    /**
     * @inheridoc
     */
    public function createConstraintFromAnnotation(): BetweenConstraint
    {
        if (isset($this->errorMessageId)) {
            return new BetweenConstraint($this->min, $this->max, $this->isInclusive, $this->errorMessageId);
        }

        return new BetweenConstraint($this->min, $this->max, $this->isInclusive);
    }
}
