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
 * Defines the base class for binder bindings to implement
 */
abstract class BinderBinding
{
    /** @var string The interface that was bound */
    protected string $interface;
    /** @var Binder The binder that registered the binding */
    protected Binder $binder;

    /**
     * @param string $interface The interface that was bound
     * @param Binder $binder The binder that registered the binding
     */
    protected function __construct(string $interface, Binder $binder)
    {
        $this->interface = $interface;
        $this->binder = $binder;
    }

    /**
     * Gets the binder that registered the binding
     *
     * @return Binder The binder that registered the binding
     */
    public function getBinder(): Binder
    {
        return $this->binder;
    }

    /**
     * Gets the interface that was bound
     *
     * @return string The interface that was bound
     */
    public function getInterface(): string
    {
        return $this->interface;
    }
}
