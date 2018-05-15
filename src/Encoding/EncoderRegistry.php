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
use DateTime;
use Opulence\Serialization\TypeResolver;
use OutOfBoundsException;

/**
 * Defines an encoder registry
 */
class EncoderRegistry
{
    /** @var IEncoder[] The mapping of types to encoders */
    private $encodersByType = [];

    /**
     * @param string $dateTimeFormat The format to use for DateTimes
     */
    public function __construct(string $dateTimeFormat = DateTime::ISO8601)
    {
        (new DefaultEncoderRegistrant($dateTimeFormat))->registerEncoder($this);
    }

    /**
     * Gets the encoder for a type
     *
     * @param string $type The type whose encoder we want
     * @return IEncoder The encoder for the input type
     * @throws OutOfBoundsException Thrown if the type does not have a encoder
     */
    public function getEncoderForType(string $type): IEncoder
    {
        $normalizedType = $this->normalizeType($type);

        if (!$this->hasEncoderForType($normalizedType)) {
            // Use the input type name to make the exception message more meaningful
            throw new OutOfBoundsException("No encoder registered for type \"$type\"");
        }

        return $this->encodersByType[$normalizedType];
    }

    /**
     * Gets the encoder for a value
     *
     * @param mixed $value The value whose encoder we want
     * @return IEncoder The encoder for the input value
     * @throws OutOfBoundsException Thrown if the value does not have an encoder
     */
    public function getEncoderForValue($value): IEncoder
    {
        // Note: The type is normalized in getEncoderForType()
        return $this->getEncoderForType(TypeResolver::resolveType($value));
    }

    /**
     * Gets whether or not the registry has a encoder for a type
     *
     * @param string $type The type to check for
     * @return bool True if the registry has a encoder for the input type, otherwise false
     */
    public function hasEncoderForType(string $type): bool
    {
        return isset($this->encodersByType[$type]);
    }

    /**
     * Registers a encoder
     *
     * @param IEncoder $encoder The encoder to register
     */
    public function registerEncoder(IEncoder $encoder): void
    {
        $normalizedType = $this->normalizeType($encoder->getType());
        $this->encodersByType[$normalizedType] = $encoder;
    }

    /**
     * Registers an object encoder
     *
     * @param string $type The type of object this encoder represents
     * @param Closure $objectFactory The factory that instantiates an object from a value
     * @param Property[] $properties,... The list of properties that make up the object
     */
    public function registerObjectEncoder(
        string $type,
        Closure $objectFactory,
        Property ...$properties
    ): void {
        $this->registerEncoder(new ObjectEncoder($type, $this, $objectFactory, ...$properties));
    }

    /**
     * Registers a struct encoder
     *
     * @param string $type The type of object this encoder represents
     * @param Closure $objectFactory The factory that instantiates an object from a value
     * @param Closure $encodingFactory The factory that encodes an instance of an object this encoder represents
     */
    public function registerStructEncoder(string $type, Closure $objectFactory, Closure $encodingFactory): void
    {
        $this->registerEncoder(new StructEncoder($type, $objectFactory, $encodingFactory));
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
