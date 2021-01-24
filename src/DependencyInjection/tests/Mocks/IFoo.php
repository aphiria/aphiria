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
 * Mocks a simple interface for use in testing
 */
interface IFoo
{
    /**
     * Gets the name of the concrete class
     *
     * @return string The name of the concrete class
     */
    public function getClassName(): string;
}
