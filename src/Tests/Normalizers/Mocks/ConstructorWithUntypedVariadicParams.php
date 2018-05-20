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
 * Mocks a class with untyped variadic constructor params
 */
class ConstructorWithUntypedVariadicParams
{
    private $foo;

    public function __construct(...$foo)
    {
        $this->foo = $foo;
    }

    public function getFoo(): array
    {
        return $this->foo;
    }
}
