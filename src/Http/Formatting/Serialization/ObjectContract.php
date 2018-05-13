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
 * Defines an object contract
 */
class ObjectContract extends Contract
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
    public function decode($objectHash, array $encodingInterceptors = []): object
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

            // Automatically decode the property value if it also has a contract
            if ($this->contracts->hasContractForType($property->getType())) {
                $propertyContract = $this->contracts->getContractForType($property->getType());
                $propertyValue = $propertyContract->decode($rawPropertyValue, $encodingInterceptors);
            } else {
                $propertyValue = $rawPropertyValue;
            }

            $convertedObjectHash[$property->getName()] = $propertyValue;
        }

        foreach ($encodingInterceptors as $encodingInterceptor) {
            $convertedObjectHash = $encodingInterceptor->onDecoding($convertedObjectHash, $this->type);
        }

        return ($this->valueFactory)($convertedObjectHash);
    }

    /**
     * @inheritdoc
     */
    public function encode($object, array $encodingInterceptors = []): array
    {
        $objectHash = [];

        foreach ($this->properties as $property) {
            $propertyValue = $property->getValue($object);

            // Automatically encode the property value if it also has a contract
            if ($this->contracts->hasContractForType($property->getType())) {
                $propertyContract = $this->contracts->getContractForType($property->getType());
                $propertyValue = $propertyContract->encode($propertyValue, $encodingInterceptors);
            }

            $objectHash[$property->getName()] = $propertyValue;
        }

        foreach ($encodingInterceptors as $encodingInterceptor) {
            $objectHash = $encodingInterceptor->onEncoding($objectHash, $this->type);
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
