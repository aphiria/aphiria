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
