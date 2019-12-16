<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Builders;

use Aphiria\Validation\ConstraintRegistry;
use Aphiria\Validation\IValidator;
use Aphiria\Validation\Validator;

/**
 * Defines the validator builder
 */
final class ValidatorBuilder
{
    /** @var ConstraintRegistry The registry of constraints to register constraints to */
    private ConstraintRegistry $constraints;

    /**
     * @param ConstraintRegistry|null $constraints The constraints to register to, or null if creating a new registry
     */
    public function __construct(ConstraintRegistry $constraints = null)
    {
        $this->constraints = $constraints ?? new ConstraintRegistry();
    }

    /**
     * Creates a validator
     *
     * @return IValidator The created validator
     */
    public function build(): IValidator
    {
        return new Validator($this->constraints);
    }

    /**
     * Adds constraints for a class
     *
     * @param string $className The name of the class to add constraints to
     * @return ObjectConstraintBuilder The constraint builder for the input class
     */
    public function class(string $className): ObjectConstraintBuilder
    {
        return new ObjectConstraintBuilder($className, $this->constraints);
    }
}
