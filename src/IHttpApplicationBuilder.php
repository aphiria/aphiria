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
 * Defines the interface for HTTP application builders to implement
 */
interface IHttpApplicationBuilder extends IApplicationBuilder
{
    /**
     * Builds the application
     */
    public function build(): void;

    /**
     * Adds routes to the application
     *
     * @param Closure $delegate The delegate that will add routes (must accept a route builder registry)
     * @return IHttpApplicationBuilder For chaining
     */
    public function withRoutes(Closure $delegate): self;
}
