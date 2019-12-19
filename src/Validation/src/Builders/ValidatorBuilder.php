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

use Aphiria\Validation\Constraints\ObjectConstraintRegistry;
use Aphiria\Validation\IValidator;
use Aphiria\Validation\Validator;

/**
 * Defines the validator builder
 */
final class ValidatorBuilder
{
    /** @var ObjectConstraintRegistry The registry of object constraints to register constraints to */
    private ObjectConstraintRegistry $objectConstraints;
    /** @var ObjectConstraintBuilder[] The list of object constraint builders created by this object */
    private array $objectConstraintBuilders = [];

    /**
     * @param ObjectConstraintRegistry|null $objectConstraints The constraints to register to, or null if creating a new registry
     */
    public function __construct(ObjectConstraintRegistry $objectConstraints = null)
    {
        $this->objectConstraints = $objectConstraints ?? new ObjectConstraintRegistry();
    }

    /**
     * Creates a validator
     *
     * @return IValidator The created validator
     */
    public function build(): IValidator
    {
        foreach ($this->objectConstraintBuilders as $objectConstraintBuilder) {
            $this->objectConstraints->registerObjectConstraints($objectConstraintBuilder->build());
        }

        return new Validator($this->objectConstraints);
    }

    /**
     * Adds constraints for a class
     *
     * @param string $className The name of the class to add constraints to
     * @return ObjectConstraintBuilder The constraint builder for the input class
     */
    public function class(string $className): ObjectConstraintBuilder
    {
        $objectConstraintBuilder = new ObjectConstraintBuilder($className);
        $this->objectConstraintBuilders[] = $objectConstraintBuilder;

        return $objectConstraintBuilder;
    }
}
