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
 * Defines a class with a typed public property that is not set in the constructor
 */
final class ConstructorWithUnsetTypedPublicProperty
{
    public int $foo = 0;

    public function __construct()
    {
        // Don't do anything
    }
}
