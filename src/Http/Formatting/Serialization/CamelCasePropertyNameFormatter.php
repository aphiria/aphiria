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
 * Defines the camelCase property name formatter interceptor
 */
class CamelCasePropertyNameFormatter implements IEncodingInterceptor
{
    /**
     * @inheritdoc
     */
    public function onDecoding($decodedValue, string $type)
    {
        // We don't handle decoding
        return $decodedValue;
    }

    /**
     * @inheritdoc
     */
    public function onEncoding($encodedValue, string $type)
    {
        if (!\is_array($encodedValue)) {
            return $encodedValue;
        }

        $camelCasedValue = [];

        foreach ($encodedValue as $key => $value) {
            $camelCasedValue[\is_string($key) ? $this->getCamelCaseString($key) : $key] = $value;
        }

        return $camelCasedValue;
    }

    /**
     * Camel-cases a string
     *
     * @param string $value The value to camelCase
     * @return string The camelCased string
     */
    private function getCamelCaseString(string $value): string
    {
        $upperCasedWords = ucwords(str_replace(['-', '_'], ' ', $value));

        return lcfirst(str_replace(' ', '', $upperCasedWords));
    }
}
