<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Serialization\Encoding;

use InvalidArgumentException;
use OutOfBoundsException;

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
     * @param EncodingContext $context The context to use while decoding
     * @return mixed An instance of the type
     * @throws EncodingException Thrown if there was an error decoding the value
     * @throws InvalidArgumentException Thrown if the input value is not of the expected type
     * @throws OutOfBoundsException Thrown if there is no encoder for the input type
     */
    public function decode($value, string $type, EncodingContext $context);

    /**
     * Encodes the input value
     *
     * @param mixed $value The value to encode
     * @param EncodingContext $context The context to use while encoding
     * @return mixed The encoded value
     * @throws EncodingException Thrown if there was an error encoding the value
     * @throws InvalidArgumentException Thrown if the input value is not of the expected type
     * @throws OutOfBoundsException Thrown if there is no encoder for the input value
     */
    public function encode($value, EncodingContext $context);
}
