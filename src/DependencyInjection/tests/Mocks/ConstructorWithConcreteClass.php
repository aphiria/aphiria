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
 * Mocks a class that takes in a concrete class in its constructor
 */
readonly class ConstructorWithConcreteClass
{
    /**
     * @param Bar $foo The object to use
     */
    public function __construct(public Bar $foo)
    {
    }
}
