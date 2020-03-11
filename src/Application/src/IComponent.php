<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application;

/**
 * Defines the interface for application components to implement
 */
interface IComponent
{
    /**
     * Actually builds the component
     *
     * Note: This will occur once services are resolvable
     */
    public function build(): void;
}
