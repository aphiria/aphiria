<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Normalizers;

use InvalidArgumentException;

/**
 * Defines the interface for normalizers to implement
 */
interface INormalizer
{
    /**
     * Denormalizes a value to an instance of the type
     *
     * @param mixed $value The value to denormalize
     * @param string $type The type to denormalize to (ending with '[]' if an array of $type)
     * @return mixed An instance of the type
     * @throws NormalizationException Thrown if there was an error denormalizing the value
     * @throws InvalidArgumentException Thrown if the input value is not of the expected type
     */
    public function denormalize($value, string $type);

    /**
     * Normalizes the input value
     *
     * @param mixed $value The value to normalize
     * @param INormalizationInterceptor[] $interceptors The list of normalizing interceptors to run through
     * @return mixed The normalized value
     * @throws NormalizationException Thrown if there was an error normalizing the value
     * @throws InvalidArgumentException Thrown if the input value is not of the expected type
     */
    public function normalize($value, array $interceptors = []);
}
