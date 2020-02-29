<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Routing\Builders;

use Aphiria\ApplicationBuilders\IApplicationBuilder;
use Aphiria\ApplicationBuilders\IComponentBuilderProxy;
use Closure;

/**
 * Defines the proxy for router builders so that they don't have to be instantiated until after bootstrappers are run
 */
final class RouterBuilderProxy extends RouterBuilder implements IComponentBuilderProxy
{
    /** @var Closure The factory that will generate the proxied instance */
    private Closure $instanceFactory;
    /** @var Closure[] The list of proxied calls to make on the underlying instance once it's resolved */
    private array $proxiedCalls = [];

    /**
     * @param Closure $instanceFactory The parameterless factory that will generate the proxied instance
     */
    public function __construct(Closure $instanceFactory)
    {
        $this->instanceFactory = $instanceFactory;
    }

    /**
     * @inheritdoc
     */
    public function build(IApplicationBuilder $appBuilder): void
    {
        /** @var RouterBuilder $instance */
        $instance = ($this->instanceFactory)();

        foreach ($this->proxiedCalls as $proxiedCall) {
            $proxiedCall($instance);
        }

        $instance->build($appBuilder);
    }

    /**
     * @inheritdoc
     */
    public function getProxiedType(): string
    {
        return RouterBuilder::class;
    }

    /**
     * @inheritdoc
     */
    public function withAnnotations(): self
    {
        $this->proxiedCalls[] = fn (RouterBuilder $routerBuilder) => $routerBuilder->withAnnotations();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withRoutes(Closure $callback): self
    {
        $this->proxiedCalls[] = fn (RouterBuilder $routerBuilder) => $routerBuilder->withRoutes($callback);

        return $this;
    }
}
