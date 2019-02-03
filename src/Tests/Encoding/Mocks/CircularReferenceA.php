<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/aphiria/serialization/blob/master/LICENSE.md
 */

namespace Aphiria\Serialization\Tests\Encoding\Mocks;

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
