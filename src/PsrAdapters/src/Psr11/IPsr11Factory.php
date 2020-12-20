<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\PsrAdapters\Psr11;

use Aphiria\DependencyInjection\IContainer;
use Psr\Container\ContainerInterface;

/**
 * Defines the interface for PSR-11 factories to implement
 */
interface IPsr11Factory
{
    /**
     * Creates a PSR-11 container from an Aphiria container
     *
     * @param IContainer $container The Aphiria container
     * @return ContainerInterface The PSR-11 container
     */
    public function createPsr11Container(IContainer $container): ContainerInterface;
}
