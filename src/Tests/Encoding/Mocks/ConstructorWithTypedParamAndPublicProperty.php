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
 * Mocks a class with a typed param and a public property
 */
class ConstructorWithTypedParamAndPublicProperty
{
    public $foo;
    private $bar;

    public function __construct(string $bar)
    {
        $this->bar = $bar;
    }

    public function getBar(): string
    {
        return $this->bar;
    }
}
