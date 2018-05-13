<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting\Serialization;

use Closure;

/**
 * Defines an array property
 */
class ArrayProperty extends Property
{
    /**
     * @inheritdoc
     */
    public function __construct(string $name, string $type, Closure $getter, bool $isNullable = false)
    {
        parent::__construct($name, $type, $getter, true, $isNullable);
    }
}
