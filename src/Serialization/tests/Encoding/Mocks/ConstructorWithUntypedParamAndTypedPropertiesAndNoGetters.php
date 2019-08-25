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
 * Defines a class with untyped parameters but with typed properties and no getters
 */
final class ConstructorWithUntypedParamAndTypedPropertiesAndNoGetters
{
    public int $foo = 0;

    public function __construct($foo)
    {
        $this->foo = $foo;
    }
}
