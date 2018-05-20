<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Tests\Normalizers\Mocks;

/**
 * Defines a constructor with untyped optional params
 */
class ConstructorWithUntypedOptionalParams
{
    private $foo;

    public function __construct($foo = 1)
    {
        $this->foo = $foo;
    }

    public function getFoo()
    {
        return $this->foo;
    }
}
