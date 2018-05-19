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
 * Defines the interface for encoders to implement
 */
interface NewIEncoder
{
    /**
     * Decodes a value to an instance of the type
     *
     * @param mixed $value The value to decode
     * @param string $type The type to decode to
     * @param bool $isArrayOfType Whether or not to decode the value as an array of a type
     * @param IEncodingInterceptor[] $interceptors The list of encoding interceptors to run through
     * @return mixed An instance of the type
     * @throws EncodingException Thrown if there was an error decoding the value
     * @throws InvalidArgumentException Thrown if the input value is not of the expected type
     */
    public function decode($value, string $type, bool $isArrayOfType, array $interceptors = []);

    /**
     * Encodes the input value
     *
     * @param mixed $value The value to encode
     * @param IEncodingInterceptor[] $interceptors The list of encoding interceptors to run through
     * @return mixed The encoded value
     * @throws EncodingException Thrown if there was an error encoding the value
     * @throws InvalidArgumentException Thrown if the input value is not of the expected type
     */
    public function encode($value, array $interceptors = []);
}
