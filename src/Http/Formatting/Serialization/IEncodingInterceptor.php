<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting\Serialization;

/**
 * Defines the interface for encoding interceptors to implement
 */
interface IEncodingInterceptor
{
    /**
     * Provides a hook for encoding data
     *
     * @param mixed $decodedValue The decoded value
     * @param string $type The type that was decoded
     * @return mixed The modified decoded value
     */
    public function onDecoding($decodedValue, string $type);

    /**
     * Provides a hook for encoding a contract
     *
     * @param mixed $encodedValue The encoded value
     * @param string $type The type of the original value that was being encoded
     * @return mixed The modified encoded value
     */
    public function onEncoding($encodedValue, string $type);
}
