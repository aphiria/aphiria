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
 * Defines the minimum constraint
 */
class MinConstraint extends Constraint
{
    /** @var string The default error message ID */
    private const DEFAULT_ERROR_MESSAGE_ID = 'Field must be more than {min}';
    /** @var int|float The minimum */
    private $min;
    /** @var bool Whether or not the minimum is inclusive */
    private bool $isInclusive;

    /**
     * @inheritdoc
     * @param int|float $min The minimum
     * @param bool $isInclusive Whether or not the minimum is inclusive
     */
    public function __construct($min, bool $isInclusive, string $errorMessageId = self::DEFAULT_ERROR_MESSAGE_ID)
    {
        parent::__construct($errorMessageId);

        if (!\is_numeric($min)) {
            throw new InvalidArgumentException('Min must be numeric');
        }

        $this->min = $min;
        $this->isInclusive = $isInclusive;
    }

    /**
     * @inheritdoc
     */
    public function getErrorMessagePlaceholders(): array
    {
        return ['min' => $this->min];
    }

    /**
     * @inheritdoc
     */
    public function passes($value, ValidationContext $validationContext): bool
    {
        if ($this->isInclusive) {
            return $value >= $this->min;
        }

        return $value > $this->min;
    }
}
