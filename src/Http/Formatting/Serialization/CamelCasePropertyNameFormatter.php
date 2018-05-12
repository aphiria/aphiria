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
        if ($type !== 'array') {
            return $decodedValue;
        }

        // Todo
    }

    /**
     * @inheritdoc
     */
    public function onEncoding($encodedValue, string $type)
    {
        if ($type !== 'array') {
            return $encodedValue;
        }

        // Todo
    }
}
