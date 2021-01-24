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
 * Mocks a class with a union type
 */
final class ConstructorWithUnionType
{
    /** @var string|IFoo The union type property */
    private string|IFoo $foo;

    /**
     * @param string|IFoo $foo The union type parameter
     */
    public function __construct(string|IFoo $foo)
    {
        $this->foo = $foo;
    }

    /**
     * @return string|IFoo
     */
    public function getFoo(): string|IFoo
    {
        return $this->foo;
    }
}
