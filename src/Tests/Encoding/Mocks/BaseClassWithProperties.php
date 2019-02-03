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
