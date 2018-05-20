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
 * Defines the array normalizer
 */
class ArrayNormalizer implements INormalizer
{
    /** @var INormalizer The parent normalizer */
    private $parentNormalizer;

    /**
     * @param INormalizer $parentNormalizer The parent normalizer
     */
    public function __construct(INormalizer $parentNormalizer)
    {
        $this->parentNormalizer = $parentNormalizer;
    }

    /**
     * @inheritdoc
     */
    public function denormalize($values, string $type)
    {
        if (substr($type, -2, 2) !== '[]') {
            throw new InvalidArgumentException('Type must end in "[]"');
        }

        if (\is_array($values)) {
            throw new InvalidArgumentException('Value must be an array');
        }

        $actualType = substr($type, 0, -2);
        $denormalizedValues = [];

        foreach ($values as $value) {
            $denormalizedValues[] = $this->parentNormalizer->denormalize($value, $actualType);
        }

        return $denormalizedValues;
    }

    /**
     * @inheritdoc
     */
    public function normalize($values, array $interceptors = [])
    {
        if (!\is_array($values)) {
            throw new InvalidArgumentException('Value must be an array');
        }

        if (\count($values) === 0) {
            return [];
        }

        $normalizedValues = [];

        foreach ($values as $value) {
            $normalizedValues[] = $this->parentNormalizer->normalize($value, $interceptors);
        }

        return $normalizedValues;
    }
}
