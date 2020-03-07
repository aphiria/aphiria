<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Console\Builders;

use Aphiria\Application\Builders\IApplicationBuilder;
use Aphiria\Application\Builders\IComponentBuilderProxy;
use Closure;

/**
 * Defines the proxy for command builders so that they don't have to be instantiated until after bootstrappers are run
 */
final class CommandBuilderProxy extends CommandBuilder implements IComponentBuilderProxy
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
        /** @var CommandBuilder $instance */
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
        return CommandBuilder::class;
    }

    /**
     * @inheritdoc
     */
    public function withAnnotations(): self
    {
        $this->proxiedCalls[] = fn (CommandBuilder $commandBuilder) => $commandBuilder->withAnnotations();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withCommands(Closure $callback): self
    {
        $this->proxiedCalls[] = fn (CommandBuilder $commandBuilder) => $commandBuilder->withCommands($callback);

        return $this;
    }
}
