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
 * Defines the DateTime formatter interceptor
 */
class DateTimeFormatter implements ISerializationInterceptor
{
    /**
     * @inheritdoc
     */
    public function onDeserialization($contract, string $type)
    {
        // Todo: return if $type isn't DateTime
    }

    /**
     * @inheritdoc
     */
    public function onSerialization($contract, string $type)
    {
        // Todo: return if $type isn't DateTime
    }
}
