<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Tests\Encoding\Mocks;

/**
 * Mocks a class with properties and no getters
 */
class ConstructorWithTypedParamsAndNoGetters
{
    private $foo;
    private $bar;

    public function __construct(string $foo, string $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}
