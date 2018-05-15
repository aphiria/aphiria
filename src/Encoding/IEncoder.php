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
     * Decodes a value to an instance of the type this encoders encodes
     *
     * @param mixed $value The value to decode
     * @param IEncodingInterceptor[] $encodingInterceptors The list of encoding interceptors to run through
     * @return mixed An instance of the type this encoder encodes
     * @throws InvalidArgumentException Thrown if the input value is not of the expected type
     * @throws EncodingException Thrown if there was an error decoding the value
     */
    public function decode($value, array $encodingInterceptors = []);

    /**
     * Encodes the input value
     *
     * @param mixed $value The value to encode
     * @param IEncodingInterceptor[] $encodingInterceptors The list of encoding interceptors to run through
     * @return mixed The encoded value
     * @throws EncodingException Thrown if there was an error encoding the value
     */
    public function encode($value, array $encodingInterceptors = []);

    /**
     * Gets the type this encoder encodes
     *
     * @return string The type this encoder encodes
     */
    public function getType(): string;
}
