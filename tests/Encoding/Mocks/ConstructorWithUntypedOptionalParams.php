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
