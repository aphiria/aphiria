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
class CircularReferenceB
{
    private $foo;

    public function __construct(CircularReferenceA $foo)
    {
        $this->foo = $foo;
    }

    public function getFoo(): CircularReferenceA
    {
        return $this->foo;
    }
}
