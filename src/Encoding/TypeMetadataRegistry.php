<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Encoding;

/**
 * Defines the registry of type metadata
 */
class TypeMetadataRegistry
{
    /** @var TypeMetadata[] The mapping of types to metadata */
    private $metadataByType = [];

    /**
     * @param string $dateTimeFormat The format to use for DateTimes
     */
    public function __construct(string $dateTimeFormat = DateTime::ISO8601)
    {
        (new DefaultMetadataRegistrant($dateTimeFormat))->registerMetadata($this);
    }

    /**
     * Gets the metadata for a type
     *
     * @param string $type The type whose metadata we want
     * @return TypeMetadata The metadata for the input type
     * @throws OutOfBoundsException Thrown if the type does not have metadata
     */
    public function getMetadataForType(string $type): TypeMetadata
    {
        $normalizedType = $this->normalizeType($type);

        if (!$this->hasMetadataForType($normalizedType)) {
            // Use the input type name to make the exception message more meaningful
            throw new OutOfBoundsException("No metadata registered for type \"$type\"");
        }

        return $this->metadataByType[$normalizedType];
    }

    /**
     * Gets the metadata for a value
     *
     * @param mixed $value The value whose metadata we want
     * @return TypeMetadata The metadata for the input value
     * @throws OutOfBoundsException Thrown if the value does not have a metadata
     */
    public function getMetadataForValue($value): TypeMetadata
    {
        // Note: The type is normalized in getMetadataForType()
        return $this->getMetadataForType(TypeResolver::resolveType($value));
    }

    /**
     * Gets whether or not the registry has a metadata for a type
     *
     * @param string $type The type to check for
     * @return bool True if the registry has a metadata for the input type, otherwise false
     */
    public function hasMetadataForType(string $type): bool
    {
        return isset($this->metadataByType[$type]);
    }

    /**
     * Registers a metadata
     *
     * @param TypeMetadata $metadata The metadata to register
     */
    public function registerMetadata(TypeMetadata $metadata): void
    {
        $normalizedType = $this->normalizeType($metadata->getType());
        $this->metadataByType[$normalizedType] = $metadata;
    }

    /**
     * Registers class metadata
     *
     * @param string $type The type of class this metadata represents
     * @param Closure $constructor The factory that instantiates an object from a value
     * @param Property[] $properties,... The list of properties that make up the object
     */
    public function registerClassMetadata(
        string $type,
        Closure $constructor,
        Property ...$properties
    ): void {
        $this->registerMetadata(new ObjectMetadata($type, $this, $constructor, ...$properties));
    }

    /**
     * Registers struct metadata
     *
     * @param string $type The type of object this metadata represents
     * @param Closure $constructor The factory that instantiates an object from a value
     * @param Closure $encodingFactory The factory that encodes an instance of an object this metadata represents
     */
    public function registerStructMetadata(string $type, Closure $constructor, Closure $encodingFactory): void
    {
        $this->registerMetadata(new StructMetadata($type, $constructor, $encodingFactory));
    }

    /**
     * Normalizes a type, eg "integer" to "int", for usage as keys in the registry
     *
     * @param string $type The type to normalize
     * @return string The normalized type
     */
    private function normalizeType(string $type): string
    {
        switch ($type) {
            case 'boolean':
            case 'bool':
                return 'bool';
            case 'float':
            case 'double':
                return 'float';
            case 'int':
            case 'integer':
                return 'int';
            default:
                return $type;
        }
    }
}
