<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Rules;

use Aphiria\Validation\ValidationContext;
use InvalidArgumentException;
use LogicException;

/**
 * Defines the between rule
 */
final class BetweenRule implements IRuleWithArgs, IRuleWithErrorPlaceholders
{
    /** @var int|float The minimum */
    protected $min;
    /** @var int|float The maximum */
    protected $max;
    /** @var bool Whether or not the extremes are inclusive */
    protected bool $isInclusive = true;

    /**
     * @inheritdoc
     */
    public function getErrorPlaceholders(): array
    {
        return ['min' => $this->min, 'max' => $this->max];
    }

    /**
     * @inheritdoc
     */
    public function getSlug(): string
    {
        return 'between';
    }

    /**
     * @inheritdoc
     */
    public function passes($value, ValidationContext $validationContext): bool
    {
        if ($this->min === null) {
            throw new LogicException('Minimum value not set');
        }

        if ($this->max === null) {
            throw new LogicException('Maximum value not set');
        }

        if ($this->isInclusive) {
            return $value >= $this->min && $value <= $this->max;
        }

        return $value > $this->min && $value < $this->max;
    }

    /**
     * @inheritdoc
     */
    public function setArgs(array $args): void
    {
        if (count($args) < 2 || !is_numeric($args[0]) || !is_numeric($args[1])) {
            throw new InvalidArgumentException('Must pass minimum and maximum values to compare against');
        }

        $this->min = $args[0];
        $this->max = $args[1];

        if (count($args) === 3 && is_bool($args[2])) {
            $this->isInclusive = $args[2];
        }
    }
}
