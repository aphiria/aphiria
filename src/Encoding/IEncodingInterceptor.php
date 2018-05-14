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
 * Defines the interface for encoding interceptors to implement
 */
interface IEncodingInterceptor
{
    /**
     * Provides a hook for post-encoding a value
     *
     * @param mixed $encodedValue The encoded value
     * @param string $type The type that is being encoded
     * @return mixed The modified encoded value
     */
    public function onPostEncoding($encodedValue, string $type);

    /**
     * Provides a hook for pre-decoding a value
     *
     * @param mixed $value The value being intercepted
     * @param string $type The type that is being decoded
     * @return mixed The modified value
     */
    public function onPreDecoding($value, string $type);
}
