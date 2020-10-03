<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Mocks;

/**
 * Mocks a class with a constructor that contains typed primitive parameters
 */
final class ConstructorWithTypedPrimitives
{
    /** @var string A primitive stored by this class */
    private string $foo;

    /**
     * @param string $foo A primitive to store in this class
     */
    public function __construct(string $foo)
    {
        $this->foo = $foo;
    }

    /**
     * @return string
     */
    public function getFoo(): string
    {
        return $this->foo;
    }
}
