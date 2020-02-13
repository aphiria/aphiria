<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
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
    /** @var IConstraint[] The list of constraints to apply on each value */
    private array $constraints;

    /**
     * @inheritdoc
     * @param IConstraint[]|IConstraint $constraints The constraint or list of constraints to apply on each value
     */
    public function __construct($constraints, string $errorMessageId = self::DEFAULT_ERROR_MESSAGE_ID)
    {
        parent::__construct($errorMessageId);

        $this->constraints = \is_array($constraints) ? $constraints : [$constraints];
    }

    /**
     * @inheritdoc
     * @throws InvalidArgumentException Thrown if the value is not an
     */
    public function passes($values): bool
    {
        if (!\is_iterable($values)) {
            throw new InvalidArgumentException('Value must be iterable');
        }

        foreach ($values as $key => $value) {
            foreach ($this->constraints as $constraint) {
                if (!$constraint->passes($value)) {
                    return false;
                }
            }
        }

        return true;
    }
}
