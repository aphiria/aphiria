<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation;

/**
 * Defines a registry of object validators
 */
final class ObjectValidatorRegistry
{
    /** @var ObjectValidator[] The mapping of class names to object validators */
    private array $objectValidators = [];

    /**
     * Gets the validator for a particular class
     *
     * @param string $class The name of the class whose validator we want
     * @return ObjectValidator|null The validator if one was found, otherwise null
     */
    public function getObjectValidator(string $class): ?ObjectValidator
    {
        if (!isset($this->objectValidators[$class])) {
            return null;
        }

        return $this->objectValidators[$class];
    }

    /**
     * Registers a validator for an object
     *
     * @param ObjectValidator $objectValidator The validator to register
     */
    public function registerObjectValidator(ObjectValidator $objectValidator): void
    {
        $this->objectValidators[$objectValidator->getClass()] = $objectValidator;
    }
}
