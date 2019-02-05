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
 * Mocks a derived class with properties
 */
class DerivedClassWithProperties extends BaseClassWithProperties
{
    private $bar;

    public function __construct(string $foo, string $bar)
    {
        parent::__construct($foo);

        $this->bar = $bar;
    }
}
