<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Mocks;

/**
 * Mocks a class that takes in primitives with default values in its constructor
 */
class ConstructorWithDefaultValuePrimitives
{
    /** @var string A primitive stored by this class */
    private string $foo;
    /** @var string A primitive stored by this class */
    private string $bar;

    /**
     * @param string $foo A primitive to store in this class
     * @param string $bar A primitive to store in this class
     */
    public function __construct($foo, $bar = 'bar')
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }

    /**
     * @return string
     */
    public function getBar(): string
    {
        return $this->bar;
    }

    /**
     * @return string
     */
    public function getFoo(): string
    {
        return $this->foo;
    }
}
