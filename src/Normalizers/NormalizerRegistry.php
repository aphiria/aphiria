<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Normalizers;

/**
 * Defines a registry of normalizers
 */
class NormalizerRegistry
{
    /** @var INormalizer[] The mapping of types to normalizers */
    private $normalizersByType = [];
    /** @var INormalizer The default object normalizer */
    private $defaultObjectNormalizer;
    /** @var INormalizer The default scalar normalizer */
    private $defaultScalarNormalizer;

    /**
     * @param INormalizer $defaultObjectNormalizer The default object normalizer
     * @param INormalizer $defaultScalarNormalizer The default scalar normalizer
     */
    public function __construct(INormalizer $defaultObjectNormalizer, INormalizer $defaultScalarNormalizer)
    {
        $this->defaultObjectNormalizer = $defaultObjectNormalizer;
        $this->defaultScalarNormalizer = $defaultScalarNormalizer;
    }

    /**
     * Gets the normalizer for a type
     *
     * @param string $type The type whose normalizer we want
     * @return INormalizer The normalizer for the input type
     */
    public function getNormalizerForType(string $type): INormalizer
    {
        $normalizedType = $this->normalizeType($type);

        if (isset($this->normalizersByType[$normalizedType])) {
            return $this->normalizersByType[$normalizedType];
        }

        if (\class_exists($type)) {
            return $this->defaultObjectNormalizer;
        }

        return $this->defaultScalarNormalizer;
    }

    /**
     * Gets the normalizer for a value
     *
     * @param mixed $value The value whose normalizer we want
     * @return INormalizer The normalizer for the input value
     * @throws OutOfBoundsException Thrown if the value does not have an normalizer
     */
    public function getNormalizerForValue($value): INormalizer
    {
        // Note: The type is normalized in getNormalizerForType()
        return $this->getNormalizerForType(TypeResolver::resolveType($value));
    }

    /**
     * Registers an normalizer
     *
     * @param string $type The type that the normalizer is for
     * @param INormalizer $normalizer The normalizer to register
     */
    public function registerNormalizer(string $type, INormalizer $normalizer): void
    {
        $normalizedType = $this->normalizeType($type);
        $this->normalizersByType[$normalizedType] = $normalizer;
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
