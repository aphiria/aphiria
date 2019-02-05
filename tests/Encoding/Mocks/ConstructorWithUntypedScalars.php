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
 * Mocks a class with untyped scalar values
 */
class ConstructorWithUntypedScalars
{
    private $foo;
    private $bar;

    public function __construct($foo, $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }

    public function getBar()
    {
        return $this->bar;
    }

    public function getFoo()
    {
        return $this->foo;
    }
}
