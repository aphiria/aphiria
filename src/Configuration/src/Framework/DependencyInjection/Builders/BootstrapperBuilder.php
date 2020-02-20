<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Configuration\Framework\DependencyInjection\Builders;

use Aphiria\Configuration\Builders\IApplicationBuilder;
use Aphiria\Configuration\Builders\IComponentBuilder;
use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper;
use Aphiria\DependencyInjection\Bootstrappers\IBootstrapperDispatcher;

/**
 * Defines the bootstrapper component builder
 */
final class BootstrapperBuilder implements IComponentBuilder
{
    /** @var IBootstrapperDispatcher The dispatcher to use */
    private IBootstrapperDispatcher $bootstrapperDispatcher;
    /** @var Bootstrapper[] The list of bootstrappers to dispatch */
    private array $bootstrappers = [];

    /**
     * @param IBootstrapperDispatcher $bootstrapperDispatcher The dispatcher to use
     */
    public function __construct(IBootstrapperDispatcher $bootstrapperDispatcher)
    {
        $this->bootstrapperDispatcher = $bootstrapperDispatcher;
    }

    /**
     * @inheritdoc
     */
    public function build(IApplicationBuilder $appBuilder): void
    {
        $this->bootstrapperDispatcher->dispatch($this->bootstrappers);
    }

    /**
     * Adds bootstrappers to dispatch
     *
     * @param Bootstrapper|Bootstrapper[] $bootstrappers The bootstrappers to add
     * @return BootstrapperBuilder For chaining
     */
    public function withBootstrappers($bootstrappers): BootstrapperBuilder
    {
        if ($bootstrappers instanceof Bootstrapper) {
            $this->bootstrappers[] = $bootstrappers;
        } elseif (\is_array($bootstrappers)) {
            $this->bootstrappers = [...$this->bootstrappers, ...$bootstrappers];
        }

        return $this;
    }
}
