<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Mocks;

/**
 * Mocks a class with a nullable object parameter
 */
final class ConstructorWithNullableObject
{
    private ?IFoo $foo;

    public function __construct(?IFoo $foo)
    {
        $this->foo = $foo;
    }

    public function getFoo(): ?IFoo
    {
        return $this->foo;
    }
}
