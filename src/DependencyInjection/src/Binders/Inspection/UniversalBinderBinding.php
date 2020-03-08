<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Binders\Inspection;

use Aphiria\DependencyInjection\Binders\Binder;

/**
 * Defines a binding that is universal
 */
final class UniversalBinderBinding extends BinderBinding
{
    /**
     * @param string $interface The interface that was bound
     * @param Binder $binder The binder that registered the binding
     */
    public function __construct(string $interface, Binder $binder)
    {
        parent::__construct($interface, $binder);
    }
}
