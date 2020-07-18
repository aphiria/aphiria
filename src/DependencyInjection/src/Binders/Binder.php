<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Binders;

use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\ResolutionException;
use RuntimeException;

/**
 * Defines the base class for binders
 */
abstract class Binder
{
    final public function __construct()
    {
        // Don't do anything
    }

    /**
     * Registers any bindings to the IoC container
     *
     * @param IContainer $container The IoC container to bind to
     * @throws ResolutionException Thrown if there was any error resolving a dependency
     * @throws RuntimeException Thrown if there was an error registering the bindings
     */
    abstract public function bind(IContainer $container): void;
}
