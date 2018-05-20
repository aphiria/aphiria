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
