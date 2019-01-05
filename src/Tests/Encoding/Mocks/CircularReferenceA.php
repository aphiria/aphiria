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
 * Mocks a class that can hold a circular reference
 */
class CircularReferenceA
{
    private $foo;

    public function getFoo(): CircularReferenceB
    {
        return $this->foo;
    }

    public function setFoo(CircularReferenceB $foo): void
    {
        $this->foo = $foo;
    }
}
