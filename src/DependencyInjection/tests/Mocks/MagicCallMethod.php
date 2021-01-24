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
 * Mocks a class that uses a __call magic method
 */
class MagicCallMethod
{
    /**
     * Handles non-existent methods
     *
     * @param string $name The name of the method called
     * @param mixed[] $arguments The arguments
     */
    public function __call($name, array $arguments)
    {
        // Don't do anything
    }
}
