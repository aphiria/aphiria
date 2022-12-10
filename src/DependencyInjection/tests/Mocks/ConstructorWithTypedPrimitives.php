<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Mocks;

/**
 * Mocks a class with a constructor that contains typed primitive parameters
 */
final readonly class ConstructorWithTypedPrimitives
{
    /**
     * @param string $foo A primitive to store in this class
     */
    public function __construct(public string $foo)
    {
    }
}
