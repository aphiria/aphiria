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
 * Defines the snake_case property name formatter interceptor
 */
class SnakeCasePropertyNameFormatter implements IEncodingInterceptor
{
    /** @var string The delimiter to use */
    private $delimiter;

    /**
     * @param string $delimiter The delimiter to use
     */
    public function __construct(string $delimiter = '_')
    {
        $this->delimiter = $delimiter;
    }

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

        $snakeCasedValue = [];

        foreach ($encodedValue as $key => $value) {
            $snakeCasedValue[\is_string($key) ? $this->getSnakeCaseString($key) : $key] = $value;
        }

        return $snakeCasedValue;
    }

    /**
     * Snake-cases a string
     *
     * @param string $value The value to snake_case
     * @return string The snake_cased string
     */
    private function getSnakeCaseString(string $value): string
    {
        $snakeCaseValue = $value;

        if (! ctype_lower($snakeCaseValue)) {
            $snakeCaseValue = preg_replace('/\s+/u', '', ucwords($snakeCaseValue));
            $snakeCaseValue = mb_strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $this->delimiter, $snakeCaseValue));
        }

        return str_replace(['-', '_'], [$this->delimiter, $this->delimiter], $snakeCaseValue);
    }
}
