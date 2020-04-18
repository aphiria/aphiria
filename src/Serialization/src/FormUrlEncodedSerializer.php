<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Serialization;

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
