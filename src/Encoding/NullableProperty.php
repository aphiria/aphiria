<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Encoding;

use Closure;

/**
 * Defines a nullable property
 */
class NullableProperty extends Property
{
    /**
     * @inheritdoc
     */
    public function __construct(string $name, string $type, Closure $getter, bool $isArrayOfType = false)
    {
        parent::__construct($name, $type, $getter, $isArrayOfType, true);
    }
}
