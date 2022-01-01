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
 * Defines an interface to implement
 */
interface IPerson
{
    /**
     * Gets the last name of the person
     *
     * @return string The last name
     */
    public function getLastName(): string;
}
