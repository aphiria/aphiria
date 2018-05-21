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

/**
 * Defines the interface for encoders to implement
 */
interface IEncoder
{
    /**
     * Decodes a value to an instance of the type
     *
     * @param mixed $value The value to decode
     * @param string $type The type to decode to (ending with '[]' if an array of $type)
     * @return mixed An instance of the type
     * @throws EncodingException Thrown if there was an error decoding the value
     * @throws InvalidArgumentException Thrown if the input value is not of the expected type
     */
    public function decode($value, string $type);

    /**
     * Encodes the input value
     *
     * @param mixed $value The value to encode
     * @return mixed The encoded value
     * @throws EncodingException Thrown if there was an error encoding the value
     * @throws InvalidArgumentException Thrown if the input value is not of the expected type
     */
    public function encode($value);
}
