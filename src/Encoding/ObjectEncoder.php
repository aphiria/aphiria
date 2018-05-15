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
use InvalidArgumentException;

/**
 * Defines an object encoder
 */
class ObjectEncoder extends Encoder
{
    /** @var EncoderRegistry The encoder registry */
    private $encoders;
    /** @var Property[] The list of properties */
    private $properties = [];
    /** @var array The mapping of normalized to original property names */
    private $normalizedPropertyNames = [];

    /**
     * @inheritdoc
     * @param EncoderRegistry $encoders The encoder registry
     * @param Property[] $properties,... The list of properties that make up the object
     */
    public function __construct(
        string $type,
        EncoderRegistry $encoders,
        Closure $objectFactory,
        Property ...$properties
    ) {
        parent::__construct($type, $objectFactory);

        $this->encoders = $encoders;

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

        foreach ($objectHash as $encodedPropertyName => $encodedPropertyValue) {
            if (($property = $this->getProperty($encodedPropertyName)) === null) {
                // This property wasn't defined in the encoder, so ignore it
                continue;
            }

            $convertedObjectHash[$property->getName()] = $this->decodePropertyValue(
                $property,
                $encodedPropertyValue,
                $encodingInterceptors
            );
        }

        foreach ($encodingInterceptors as $encodingInterceptor) {
            $convertedObjectHash = $encodingInterceptor->onPreDecoding($convertedObjectHash, $this->type);
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
            $objectHash[$property->getName()] = $this->encodePropertyValue(
                $property,
                $propertyValue,
                $encodingInterceptors
            );
        }

        foreach ($encodingInterceptors as $encodingInterceptor) {
            $objectHash = $encodingInterceptor->onPostEncoding($objectHash, $this->type);
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

    /**
     * Decodes a property value
     *
     * @param Property $property The property whose value we're decoding
     * @param mixed $encodedPropertyValue The encoded property value
     * @param IEncodedInterceptor[] $encodingInterceptors The list of encoding interceptors
     * @return mixed The decoded property value
     * @throws EncodingException Thrown if a non-nullable value is null
     */
    private function decodePropertyValue(Property $property, $encodedPropertyValue, array $encodingInterceptors)
    {
        // Automatically decode the property value if it also has an encoder
        if ($encodedPropertyValue === null) {
            if (!$property->isNullable()) {
                throw new EncodingException("Property {$property->getName()} cannot be null");
            }

            // Purposely don't go to the encoder for this type - just set the decoded value to null
            return null;
        }

        if (!$this->encoders->hasEncoderForType($property->getType())) {
            return $encodedPropertyValue;
        }

        $propertyEncoder = $this->encoders->getEncoderForType($property->getType());

        if ($property->isArrayOfType()) {
            $decodedPropertyValue = [];

            foreach ($encodedPropertyValue as $singlePropertyValue) {
                $decodedPropertyValue[] = $propertyEncoder->decode($singlePropertyValue, $encodingInterceptors);
            }

            return $decodedPropertyValue;
        }

        return $propertyEncoder->decode($encodedPropertyValue, $encodingInterceptors);
    }

    /**
     * Encodes a property value
     *
     * @param Property $property The property whose value we're encoding
     * @param mixed $propertyValue The property value to encode
     * @param array $encodingInterceptors The list of encoding interceptors
     * @return mixed The encoded property value
     * @throws EncodingException Thrown if a non-nullable value is null
     */
    private function encodePropertyValue(Property $property, $propertyValue, array $encodingInterceptors)
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

        $propertyEncoder = $this->encoders->getEncoderForType($property->getType());

        if ($property->isArrayOfType()) {
            $encodedPropertyValue = [];

            foreach ($propertyValue as $singlePropertyValue) {
                $encodedPropertyValue[] = $propertyEncoder->encode($singlePropertyValue, $encodingInterceptors);
            }

            return $encodedPropertyValue;
        }

        return $propertyEncoder->encode($propertyValue, $encodingInterceptors);
    }
}
