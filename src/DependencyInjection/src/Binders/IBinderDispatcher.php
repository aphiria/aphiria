<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Binders;

/**
 * Defines the interface for binder dispatchers to implement
 */
interface IBinderDispatcher
{
    /**
     * Dispatches binders
     *
     * @param Binder[] $binders The binders to dispatch
     */
    public function dispatch(array $binders): void;
}
