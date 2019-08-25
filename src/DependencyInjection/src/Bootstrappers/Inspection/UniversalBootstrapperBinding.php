<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/dependency-injection/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Bootstrappers\Inspection;

use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper;

/**
 * Defines a binding that is universal
 */
final class UniversalBootstrapperBinding extends BootstrapperBinding
{
    /**
     * @param string $interface The interface that was bound
     * @param Bootstrapper $bootstrapper The bootstrapper that registered the binding
     */
    public function __construct(string $interface, Bootstrapper $bootstrapper)
    {
        parent::__construct($interface, $bootstrapper);
    }
}
