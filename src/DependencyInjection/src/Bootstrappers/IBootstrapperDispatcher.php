<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Bootstrappers;

/**
 * Defines the interface for bootstrapper dispatchers to implement
 */
interface IBootstrapperDispatcher
{
    /**
     * Dispatches bootstrappers
     *
     * @param Bootstrapper[] $bootstrappers The bootstrappers to dispatch
     */
    public function dispatch(array $bootstrappers): void;
}
