<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints;

use Aphiria\Validation\ValidationContext;
use InvalidArgumentException;

/**
 * Defines the maximum constraint
 */
class MaxConstraint extends ValidationConstraint
{
    /** @var int|float The maximum */
    private $max;
    /** @var bool Whether or not the maximum is inclusive */
    private bool $isInclusive;

    /**
     * @inheritdoc
     * @param int|float $max The maximum
     * @param bool $isInclusive Whether or not the maximum is inclusive
     */
    public function __construct($max, bool $isInclusive, string $errorMessageId)
    {
        parent::__construct($errorMessageId);

        if (!\is_numeric($max)) {
            throw new InvalidArgumentException('Max must be numeric');
        }

        $this->max = $max;
        $this->isInclusive = $isInclusive;
    }

    /**
     * @inheritdoc
     */
    public function getErrorMessagePlaceholders(): array
    {
        return ['max' => $this->max];
    }

    /**
     * @inheritdoc
     */
    public function passes($value, ValidationContext $validationContext): bool
    {
        if ($this->isInclusive) {
            return $value <= $this->max;
        }

        return $value < $this->max;
    }
}
