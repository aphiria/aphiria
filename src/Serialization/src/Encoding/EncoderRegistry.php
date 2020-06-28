<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Serialization\Encoding;

use Aphiria\Reflection\TypeResolver;
use OutOfBoundsException;

/**
 * Defines a registry of encoders
 */
final class EncoderRegistry
{
    /** @var IEncoder[] The mapping of types to encoders */
    private array $encodersByType = [];
    /** @var IEncoder|null The default object encoder */
    private ?IEncoder $defaultObjectEncoder = null;
    /** @var IEncoder|null The default scalar encoder */
    private ?IEncoder $defaultScalarEncoder = null;

    /**
     * Gets the encoder for a type
     *
     * @param string $type The type whose encoder we want
     * @return IEncoder The encoder for the input type
     */
    public function getEncoderForType(string $type): IEncoder
    {
        $normalizedType = self::normalizeType($type);

        if (isset($this->encodersByType[$normalizedType])) {
            return $this->encodersByType[$normalizedType];
        }

        if (class_exists($type)) {
            if ($this->defaultObjectEncoder === null) {
                throw new OutOfBoundsException("No default object encoder is registered for type $type");
            }

            return $this->defaultObjectEncoder;
        }

        if ($this->defaultScalarEncoder === null) {
            throw new OutOfBoundsException("No default scalar encoder is registered for type $type");
        }

        return $this->defaultScalarEncoder;
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
        // Note: The type is encoded in getEncoderForType()
        return $this->getEncoderForType(TypeResolver::resolveType($value));
    }

    /**
     * Registers a default encoder for objects
     *
     * @param IEncoder $encoder The default object encoder
     */
    public function registerDefaultObjectEncoder(IEncoder $encoder): void
    {
        $this->defaultObjectEncoder = $encoder;
    }

    /**
     * Registers a default encoder for scalars
     *
     * @param IEncoder $encoder The default scalar encoder
     */
    public function registerDefaultScalarEncoder(IEncoder $encoder): void
    {
        $this->defaultScalarEncoder = $encoder;
    }

    /**
     * Registers an encoder
     *
     * @param string $type The type that the encoder is for
     * @param IEncoder $encoder The encoder to register
     */
    public function registerEncoder(string $type, IEncoder $encoder): void
    {
        $normalizedType = self::normalizeType($type);
        $this->encodersByType[$normalizedType] = $encoder;
    }

    /**
     * Normalizes a type, eg "integer" to "int", for usage as keys in the registry
     *
     * @param string $type The type to normalize
     * @return string The normalized type
     */
    private static function normalizeType(string $type): string
    {
        switch (strtolower($type)) {
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
                if (substr($type, -2) === '[]') {
                    return 'array';
                }

                return $type;
        }
    }
}
