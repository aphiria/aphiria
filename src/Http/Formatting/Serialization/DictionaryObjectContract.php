<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting\Serialization;

use Closure;
use InvalidArgumentException;

/**
 * Defines a dictionary object contract
 */
class DictionaryObjectContract extends ObjectContract
{
    /** @var ContractRegistry The contract registry */
    private $contracts;
    /** @var Property[] The list of properties */
    private $properties = [];
    /** @var array The mapping of normalized to original property names */
    private $normalizedPropertyNames = [];

    /**
     * @inheritdoc
     * @param ContractRegistry $contracts The contract registry
     * @param Property[] $properties,... The list of properties that make up the object
     */
    public function __construct(
        string $type,
        ContractRegistry $contracts,
        Closure $objectFactory,
        Property ...$properties
    ) {
        parent::__construct($type, $objectFactory);

        $this->contracts = $contracts;

        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $this->properties[$propertyName] = $property;
            // Set up fuzzy matching
            $this->normalizedPropertyNames[$this->normalizePropertyName($propertyName)] = $propertyName;
        }
    }

    /**
     * @inheritdoc
     */
    public function createObject($objectHash): object
    {
        if (!\is_array($objectHash)) {
            throw new InvalidArgumentException('Value must be an associative array of properties');
        }

        $convertedObjectHash = [];

        foreach ($objectHash as $rawPropertyName => $rawPropertyValue) {
            if (($property = $this->getProperty($rawPropertyName)) === null) {
                // This property wasn't defined in the contract, so ignore it
                continue;
            }

            // Automatically create an object for the property if it also has a contract
            if ($this->contracts->hasContractForType($property->getType())) {
                $propertyContract = $this->contracts->getContractForType($property->getType());
                $propertyValue = $propertyContract->createObject($rawPropertyValue);
            } else {
                $propertyValue = $rawPropertyValue;
            }

            $convertedObjectHash[$property->getName()] = $propertyValue;
        }

        return ($this->objectFactory)($convertedObjectHash);
    }

    /**
     * @inheritdoc
     */
    public function createPhpValue(object $object): array
    {
        $objectHash = [];

        foreach ($this->properties as $property) {
            $propertyValue = $property->getValue($object);

            // Automatically create a value for the property if it also has a contract
            if ($this->contracts->hasContractForType($property->getType())) {
                $propertyContract = $this->contracts->getContractForType($property->getType());
                $propertyValue = $propertyContract->createPhpValue($propertyValue);
            }

            $objectHash[$property->getName()] = $propertyValue;
        }

        return $objectHash;
    }

    /**
     * Gets the property with the input name
     *
     * @param string $propertyName The name of the property we want (with fuzzy matching fallback)
     * @return Property|null The property if one existed, otherwise null
     */
    protected function getProperty(string $propertyName): ?Property
    {
        // First, try an exact match
        if (isset($this->properties[$propertyName])) {
            return $this->properties[$propertyName];
        }

        // Next, try fuzzy matching
        $normalizedPropertyName = $this->normalizePropertyName($propertyName);

        if (isset($this->normalizedPropertyNames[$normalizedPropertyName])) {
            return $this->properties[$this->normalizedPropertyNames[$normalizedPropertyName]];
        }

        return null;
    }

    /**
     * Normalizes a property name to support fuzzy matching
     *
     * @param string $propertyName The property name to normalize
     * @return string The normalized property name
     */
    protected function normalizePropertyName(string $propertyName): string
    {
        return strtolower(str_replace('_', '', $propertyName));
    }
}
