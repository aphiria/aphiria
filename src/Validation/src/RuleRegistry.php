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

use Aphiria\Validation\Rules\IRule;

/**
 * Defines the registry of rules for various object properties and methods
 */
final class RuleRegistry
{
    /** @var array The mapping of class names to an array of property names and rules */
    private array $classesToPropertyRules = [];
    /** @var array The mapping of class names to an array of method names and rules */
    private array $classesToMethodRules = [];

    /**
     * Gets the rules associated with a particular method
     *
     * @param string $className The name of the class that contains the method
     * @param string $methodName The name of the method whose rules we want
     * @return IRule[] The list of rules for the method
     */
    public function getMethodRules(string $className, string $methodName): array
    {
        if (!isset($this->classesToMethodRules[$className][$methodName])) {
            return [];
        }

        return $this->classesToMethodRules[$className][$methodName];
    }

    /**
     * Gets the rules associated with all methods
     *
     * @param string $className The name of the class to search
     * @return IRule[] The mapping of method names to rules
     */
    public function getAllMethodRules(string $className): array
    {
        if (!isset($this->classesToMethodRules[$className])) {
            return [];
        }

        return $this->classesToMethodRules[$className];
    }

    /**
     * Gets the rules associated with all properties
     *
     * @param string $className The name of the class to search
     * @return IRule[] The mapping of property names to rules
     */
    public function getAllPropertyRules(string $className): array
    {
        if (!isset($this->classesToPropertyRules[$className])) {
            return [];
        }

        return $this->classesToPropertyRules[$className];
    }

    /**
     * Gets the rules associated with a particular property
     *
     * @param string $className The name of the class that contains the property
     * @param string $propertyName The name of the property whose rules we want
     * @return IRule[] The list of rules for the property
     */
    public function getPropertyRules(string $className, string $propertyName): array
    {
        if (!isset($this->classesToPropertyRules[$className][$propertyName])) {
            return [];
        }

        return $this->classesToPropertyRules[$className][$propertyName];
    }

    /**
     * Registers rules for a particular class method
     *
     * @param string $className The name of the class that contains the method
     * @param string $methodName The name of the method whose rules we're registering
     * @param IRule[]|IRule $rules The rule or list of rules to register
     */
    public function registerMethodRules(string $className, string $methodName, $rules): void
    {
        if (!\is_array($rules)) {
            $rules = [$rules];
        }

        if (!isset($this->classesToMethodRules[$className])) {
            $this->classesToMethodRules[$className] = [];
        }

        $this->classesToMethodRules[$className][$methodName] = $rules;
    }

    /**
     * Registers rules for a particular class property
     *
     * @param string $className The name of the class that contains the property
     * @param string $propertyName The name of the property whose rules we're registering
     * @param IRule[]|IRule $rules The rule or list of rules to register
     */
    public function registerPropertyRules(string $className, string $propertyName, $rules): void
    {
        if (!\is_array($rules)) {
            $rules = [$rules];
        }

        if (!isset($this->classesToPropertyRules[$className])) {
            $this->classesToPropertyRules[$className] = [];
        }

        $this->classesToPropertyRules[$className][$propertyName] = $rules;
    }
}
