<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Encoding;

use InvalidArgumentException;
use OutOfBoundsException;

/**
 * Defines the encoder
 */
class NewEncoder implements NewIEncoder
{
    /** @var TypeMetadataRegistry The registry of type metadata */
    private $typeMetadataRegistry;

    /**
     * @param TypeMetadataRegistry $typeMetadataRegistry The registry of type metadata
     */
    public function __construct(TypeMetadataRegistry $typeMetadataRegistry)
    {
        $this->typeMetadataRegistry = $typeMetadataRegistry;
    }

    /**
     * @inheritdoc
     */
    public function decode($value, string $type, bool $isArrayOfType, array $interceptors = [])
    {
        try {
            $typeMetadata = $this->typeMetadataRegistry->getMetadataForType($type);

            if (!$isArrayOfType) {
                return $this->decodeWithTypeMetadata($value, $typeMetadata, $interceptors);
            }

            $decodedValues = [];

            foreach ($value as $singleValue) {
                $decodedValues[] = $this->decodeWithTypeMetadata($singleValue, $typeMetadata, $interceptors);
            }

            return $decodedValues;
        } catch (OutOfBoundsException $ex) {
            throw new EncodingException("Failed to decode type $type", 0, $ex);
        }
    }

    /**
     * @inheritdoc
     */
    public function encode($value, array $interceptors = [])
    {
        $isArrayOfType = \is_array($value) && !self::arrayIsAssociative($value);

        try {
            if (!$isArrayOfType) {
                $typeMetadata = $this->typeMetadataRegistry->getMetadataForValue($value);

                return $this->encodeWithTypeMetadata($value, $typeMetadata, $interceptors);
            }

            if (\count($value) === 0) {
                return [];
            }

            // Here we assume the list contains homogenous types
            $typeMetadata = $this->typeMetadataRegistry->getMetadataForValue($value[0]);
            $encodedValues = [];

            foreach ($value as $singleValue) {
                $encodedValues[] = $this->encodeWithTypeMetadata($singleValue, $typeMetadata, $interceptors);
            }

            return $encodedValues;
        } catch (OutOfBoundsException $ex) {
            throw new EncodingException('Failed to encode value', 0, $ex);
        }
    }

    /**
     * Decodes an object hash to an instance of the type
     *
     * @param array $objectHash The object hash to decode
     * @param ClassMetadata $metadata The metadata for the type to instantiate
     * @param IEncodingInterceptor[] $interceptors The list of encoding interceptors to run through
     * @return mixed An instance of the type
     * @throws EncodingException Thrown if there was an error decoding the value
     */
    protected function decodeObject(array $objectHash, ClassMetadata $metadata, array $interceptors)
    {
        $convertedObjectHash = [];

        foreach ($objectHash as $encodedPropertyName => $encodedPropertyValue) {
            if (($property = $metadata->getProperty($encodedPropertyName)) === null) {
                // This property wasn't defined in the metadata, so ignore it
                continue;
            }

            $convertedObjectHash[$property->getName()] = $this->decodePropertyValue(
                $property,
                $encodedPropertyValue,
                $interceptors
            );
        }

        foreach ($interceptors as $interceptor) {
            $convertedObjectHash = $interceptor->onPreDecoding($convertedObjectHash, $this->type);
        }

        return ($metadata->getConstructor())($convertedObjectHash);
    }

    /**
     * Decodes a property value
     *
     * @param Property $property The property whose value we're decoding
     * @param mixed $encodedPropertyValue The encoded property value
     * @param IEncodedInterceptor[] $interceptors The list of encoding interceptors
     * @return mixed The decoded property value
     * @throws EncodingException Thrown if a non-nullable value is null
     */
    protected function decodePropertyValue(Property $property, $encodedPropertyValue, array $interceptors)
    {
        // Automatically decode the property value if it also has an encoder
        if ($encodedPropertyValue === null) {
            if (!$property->isNullable()) {
                throw new EncodingException("Property {$property->getName()} cannot be null");
            }

            // Purposely don't go to the encoder for this type - just set the decoded value to null
            return null;
        }

        if (!$this->typeMetadataRegistry->hasMetadataForType($property->getType())) {
            return $encodedPropertyValue;
        }

        return $this->decode($encodedPropertyValue, $property->getType(), $property->isArrayOfType(), $interceptors);
    }

    /**
     * Decodes a value to an instance of the struct
     *
     * @param mixed $value The value to decode
     * @param StructMetadata $metadata The metadata for the struct
     * @param IEncodingInterceptor[] $interceptors The list of encoding interceptors to run through
     * @return mixed An instance of the type
     * @throws InvalidArgumentException Thrown if the input value is not of the expected type
     * @throws EncodingException Thrown if there was an error decoding the value
     */
    protected function decodeStruct($value, StructMetadata $metadata, array $interceptors)
    {
        foreach ($interceptors as $interceptor) {
            $value = $interceptor->onPreDecoding($value, $metadata->getType());
        }

        return ($metadata->getConstructor())($value);
    }

    /**
     * Decodes a value using the type metadata
     *
     * @param mixed $value The value to decode
     * @param TypeMetadata $typeMetadata The type metadata
     * @param IEncodingInterceptor[] $interceptors The encoding interceptors to run through
     * @return mixed The decoded value
     * @throws InvalidArgumentException Thrown if the type metadata or value were an unexpected type
     * @throws EncodingException Thrown if there was an error decoding the data
     */
    protected function decodeWithTypeMetadata($value, TypeMetadata $typeMetadata, array $interceptors)
    {
        if ($typeMetadata instanceof ClassMetadata) {
            if (!\is_array($value) || !self::arrayIsAssociative(value)) {
                throw new InvalidArgumentException('Value to decode must be an associative array');
            }

            return $this->decodeObject($value, $typeMetadata, $interceptors);
        }

        if ($typeMetadata instanceof StructMetadata) {
            return $this->decodeStruct($value, $typeMetadata, $interceptors);
        }

        throw new InvalidArgumentException('Unexpected type metadata ' . get_class($typeMetadata));
    }

    /**
     * Encodes an object
     *
     * @param object $object The object to encode
     * @param ClassMetadata $metadata The object's metadata
     * @param array $interceptors The encoding interceptors to run through
     * @return array The encoded object
     */
    protected function encodeObject(object $object, ClassMetadata $metadata, array $interceptors): array
    {
        $objectHash = [];

        foreach ($metadata->getProperties() as $property) {
            $propertyValue = $property->getValue($object);
            $objectHash[$property->getName()] = $this->encodePropertyValue(
                $property,
                $propertyValue,
                $interceptors
            );
        }

        foreach ($interceptors as $interceptor) {
            $objectHash = $interceptor->onPostEncoding($objectHash, $metadata->getType());
        }

        return $objectHash;
    }

    /**
     * Encodes a property value
     *
     * @param Property $property The property whose value we're encoding
     * @param mixed $propertyValue The property value to encode
     * @param IEncodingInterceptor[] $interceptors The encoding interceptors to run through
     * @return mixed The encoded property value
     * @throws EncodingException Thrown if a non-nullable value is null
     */
    protected function encodePropertyValue(Property $property, $propertyValue, array $interceptors)
    {
        // Automatically encode the property value if it also has an encoder
        if ($propertyValue === null) {
            if (!$property->isNullable()) {
                throw new EncodingException("Property {$property->getName()} cannot be null");
            }

            // Purposely don't go to the encoder for this type - just set the encoded value to null
            return null;
        }

        if (!$this->encoders->hasEncoderForType($property->getType())) {
            return $propertyValue;
        }

        return $this->encode($propertyValue, $interceptors);
    }

    /**
     * Encodes a struct value
     *
     * @param mixed $value The value to encode
     * @param StructMetadata $metadata The metadata about the value
     * @param IEncodingInterceptor[] $interceptors The encoding interceptors to run through
     * @return mixed The encoded struct value
     */
    protected function encodeStruct($value, StructMetadata $metadata, array $interceptors)
    {
        $encodedValue = ($metadata->getSerializer())($value);

        foreach ($interceptors as $interceptor) {
            $encodedValue = $interceptor->onPostEncoding($encodedValue, $metadata->getType());
        }

        return $encodedValue;
    }

    /**
     * Encodes a value using the type metadata
     *
     * @param mixed $value The value to encode
     * @param TypeMetadata $typeMetadata The type metadata
     * @param IEncodingInterceptor[] $interceptors The encoding interceptors to run through
     * @return mixed The encoded value
     * @throws InvalidArgumentException Thrown if the type metadata or value were an unexpected type
     * @throws EncodingException Thrown if there was an error encoding the data
     */
    protected function encodeWithTypeMetadata($value, TypeMetadata $typeMetadata, array $interceptors)
    {
        if ($typeMetadata instanceof ClassMetadata) {
            if (\is_object($value)) {
                throw new InvalidArgumentException('Value to encode must be an object');
            }

            return $this->encodeObject($value, $typeMetadata, $interceptors);
        }

        if ($typeMetadata instanceof StructMetadata) {
            return $this->encodeStruct($value, $typeMetadata, $interceptors);
        }

        throw new InvalidArgumentException('Unexpected type metadata ' . get_class($typeMetadata));
    }

    /**
     * Gets whether or not the input array is associative
     *
     * @param array $value The value to check
     * @return bool True if the input array is associative, otherwise false
     */
    private static function arrayIsAssociative(array $value): bool
    {
        return count(array_filter(array_keys($value), 'is_string')) > 0;
    }
}
