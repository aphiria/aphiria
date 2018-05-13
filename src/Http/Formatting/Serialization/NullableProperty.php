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
 * Defines a nullable property
 */
class NullableProperty extends Property
{
    /**
     * @inheritdoc
     */
    public function isNullable(): bool
    {
        return true;
    }
}
