<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Tests\Encoding\Mocks;

/**
 * Mocks a base class with properties
 */
abstract class BaseClassWithProperties
{
    protected $foo;

    protected function __construct(string $foo)
    {
        $this->foo = $foo;
    }
}
