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
 * Mocks a class that takes in an interface in its constructor
 */
readonly class ConstructorWithInterface
{
    /**
     * @param IFoo $foo The object to use
     */
    public function __construct(public IFoo $foo)
    {
    }
}
