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
 * Defines a constraint that can be applied to all values of an iterable value
 */
final class ForEachConstraint extends ValidationConstraint
{
    /** @var IValidationConstraint[] The list of constraints to apply on each value */
    private array $constraints;

    /**
     * @inheritdoc
     * @param IValidationConstraint[]|IValidationConstraint $constraints The constraint or list of constraints to apply on each value
     */
    public function __construct($constraints, string $errorMessageId)
    {
        parent::__construct($errorMessageId);

        $this->constraints = \is_array($constraints) ? $constraints : [$constraints];
    }

    /**
     * @inheritdoc
     * @throws InvalidArgumentException Thrown if the value is not an
     */
    public function passes($values, ValidationContext $validationContext): bool
    {
        if (!\is_iterable($values)) {
            throw new InvalidArgumentException('Value must be iterable');
        }

        foreach ($values as $key => $value) {
            foreach ($this->constraints as $constraint) {
                // We don't pass in a new validation context because the context is in terms of the entire array, not individual values
                if (!$constraint->passes($value, $validationContext)) {
                    return false;
                }
            }
        }

        return true;
    }
}
