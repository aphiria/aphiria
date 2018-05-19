<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Encoding;

use Closure;

/**
 * Defines class metadata
 */
class ClassMetadata extends TypeMetadata
{
    /** @var Property[] The list of properties */
    private $properties = [];
    /** @var array The mapping of normalized to original property names */
    private $normalizedPropertyNames = [];

    /**
     * @inheritdoc
     */
    public function __construct(string $type, Closure $constructor, Property ...$properties)
    {
        parent::__construct($type, $constructor);

        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $this->properties[$propertyName] = $property;
            // Set up fuzzy matching
            $this->normalizedPropertyNames[$this->normalizePropertyName($propertyName)] = $propertyName;
        }
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
     * Gets the list of properties in this class
     *
     * @return Property[] The list of properties
     */
    public function getProperties(): array
    {
        return $this->properties;
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
