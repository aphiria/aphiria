<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Serialization\Tests\Encoding\Mocks;

/**
 * Mocks a class with an untyped param and with a typed property that differs from the type on the getter
 */
final class ConstructorWithUntypedParamAndTypedPropertyAndTypedGetter
{
    public string $foo;

    public function __construct($foo)
    {
        $this->foo = $foo;
    }

    public function getFoo(): int
    {
        return 1;
    }
}
