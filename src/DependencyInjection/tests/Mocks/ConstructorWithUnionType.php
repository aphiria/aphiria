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
    /**
     * @param string|IFoo $foo The union type parameter
     */
    public function __construct(public readonly string|IFoo $foo)
    {
    }
}
