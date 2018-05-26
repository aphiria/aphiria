<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization;

/**
 * Defines the form URL-encoded serializer
 */
class FormUrlEncodedSerializer extends Serializer
{
    /**
     * @inheritdoc
     */
    public function deserialize(string $value, string $type)
    {
        $encodedValue = [];
        parse_str($value, $encodedValue);

        return $this->decode($encodedValue, $type);
    }

    /**
     * @inheritdoc
     */
    public function serialize($value): string
    {
        return http_build_query($this->encode($value));
    }
}
