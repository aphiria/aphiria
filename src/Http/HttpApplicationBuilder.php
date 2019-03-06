<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/configuration/blob/master/LICENSE.md
 */

namespace Aphiria\Configuration\Http;

use Aphiria\Configuration\ApplicationBuilder;
use Aphiria\Routing\Builders\RouteBuilderRegistry;
use Closure;
use Opulence\Ioc\Bootstrappers\IBootstrapperRegistry;

final class HttpApplicationBuilder extends ApplicationBuilder implements IHttpApplicationBuilder
{
    /** @var RouteBuilderRegistry The route builders to use in delegates */
    private $routeBuilders;
    /** @var CLosure[] The list of route delegates */
    private $routeDelegates = [];

    /**
     * @inheritdoc
     * @param RouteBuilderRegistry $routeBuilders The route builders to use in delegates
     */
    public function __construct(RouteBuilderRegistry $routeBuilders, IBootstrapperRegistry $bootstrappers)
    {
        parent::__construct($bootstrappers);

        $this->routeBuilders = $routeBuilders;
    }

    /**
     * @inheritdoc
     */
    public function build(): void
    {
        $this->runBootstrapperDelegates();

        foreach ($this->routeDelegates as $routeDelegate) {
            $routeDelegate($this->routeBuilders);
        }
    }

    /**
     * @inheritdoc
     */
    public function withModule(IHttpModuleBuilder $moduleBuilder): IHttpApplicationBuilder
    {
        $moduleBuilder->build($this);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withRoutes(Closure $delegate): IHttpApplicationBuilder
    {
        $this->routeDelegates[] = $delegate;

        return $this;
    }
}
