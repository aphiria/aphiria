<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Bootstrappers\Inspection;

use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper;

/**
 * Defines the base class for bootstrapper bindings to implement
 */
abstract class BootstrapperBinding
{
    /** @var string The interface that was bound */
    protected string $interface;
    /** @var Bootstrapper The bootstrapper that registered the binding */
    protected Bootstrapper $bootstrapper;

    /**
     * @param string $interface The interface that was bound
     * @param Bootstrapper $bootstrapper The bootstrapper that registered the binding
     */
    protected function __construct(string $interface, Bootstrapper $bootstrapper)
    {
        $this->interface = $interface;
        $this->bootstrapper = $bootstrapper;
    }

    /**
     * Gets the bootstrapper that registered the binding
     *
     * @return Bootstrapper The bootstrapper that registered the binding
     */
    public function getBootstrapper(): Bootstrapper
    {
        return $this->bootstrapper;
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
