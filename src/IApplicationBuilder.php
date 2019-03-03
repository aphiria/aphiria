<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/configuration/blob/master/LICENSE.md
 */

namespace Aphiria\Configuration;

use Closure;

/**
 * Defines the interface for application builders to implement
 */
interface IApplicationBuilder
{
    /**
     * Adds bootstrappers to the application
     *
     * @param Closure $delegate The delegate that will add bootstrappers (must accept a boostrapper registry)
     * @return IApplicationBuilder For chaining
     */
    public function withBootstrappers(Closure $delegate): self;
}
