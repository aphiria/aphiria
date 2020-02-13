<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Serialization\Tests\Encoding\Mocks;

/**
 * Mocks a class with untyped variadic constructor params
 */
class ConstructorWithUntypedVariadicParams
{
    private $foo;

    public function __construct(...$foo)
    {
        $this->foo = $foo;
    }

    public function getFoo(): array
    {
        return $this->foo;
    }
}
