<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Binders;

use Aphiria\DependencyInjection\IContainer;

/**
 * Defines the interface for binder dispatchers to implement
 */
interface IBinderDispatcher
{
    /**
     * Dispatches binders
     *
     * @param list<Binder> $binders The binders to dispatch
     * @param IContainer $container The container to dispatch the binders with
     */
    public function dispatch(array $binders, IContainer $container): void;
}
