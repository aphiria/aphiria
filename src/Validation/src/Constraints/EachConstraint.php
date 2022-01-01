<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints;

use InvalidArgumentException;

/**
 * Defines a constraint that can be applied to all values of an iterable value
 */
final class EachConstraint extends Constraint
{
    /** @var string The default error message ID */
    private const DEFAULT_ERROR_MESSAGE_ID = 'Field is invalid';
    /** @var list<IConstraint> The list of constraints to apply on each value */
    private readonly array $constraints;

    /**
     * @inheritdoc
     * @param list<IConstraint>|IConstraint $constraints The constraint or list of constraints to apply on each value
     */
    public function __construct(IConstraint|array $constraints, string $errorMessageId = self::DEFAULT_ERROR_MESSAGE_ID)
    {
        parent::__construct($errorMessageId);

        $this->constraints = \is_array($constraints) ? $constraints : [$constraints];
    }

    /**
     * @inheritdoc
     * @throws InvalidArgumentException Thrown if the value is not an
     */
    public function passes($value): bool
    {
        if (!\is_iterable($value)) {
            throw new InvalidArgumentException('Value must be iterable');
        }

        /** @psalm-suppress MixedAssignment The single value is meant to be mixed */
        foreach ($value as $singleValue) {
            foreach ($this->constraints as $constraint) {
                if (!$constraint->passes($singleValue)) {
                    return false;
                }
            }
        }

        return true;
    }
}
