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
 * Mocks a class with nullable constructor params
 */
class ConstructorWithNullableParams
{
    private $foo;

    public function __construct(?string $foo)
    {
        $this->foo = $foo;
    }

    public function getFoo(): ?string
    {
        return $this->foo;
    }
}
