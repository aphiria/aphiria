<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\DependencyInjection\Components;

use Aphiria\Application\IComponent;
use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper;
use Aphiria\DependencyInjection\Bootstrappers\IBootstrapperDispatcher;

/**
 * Defines the bootstrapper component
 */
class BootstrapperComponent implements IComponent
{
    /** @var IBootstrapperDispatcher The bootstrapper dispatcher */
    private IBootstrapperDispatcher $bootstrapperDispatcher;
    /** @var Bootstrapper[] The list of bootstrappers to dispatch */
    private array $bootstrappers = [];

    /**
     * @param IBootstrapperDispatcher $bootstrapperDispatcher the bootstrapper dispatcher
     */
    public function __construct(IBootstrapperDispatcher $bootstrapperDispatcher)
    {
        $this->bootstrapperDispatcher = $bootstrapperDispatcher;
    }

    /**
     * @inheritdoc
     */
    public function initialize(): void
    {
        $this->bootstrapperDispatcher->dispatch($this->bootstrappers);
    }

    /**
     * Adds bootstrappers to dispatch
     *
     * @param Bootstrapper|Bootstrapper[] $bootstrappers The bootstrappers to add
     * @return self For chaining
     */
    public function withBootstrappers($bootstrappers): self
    {
        if ($bootstrappers instanceof Bootstrapper) {
            $this->bootstrappers[] = $bootstrappers;
        } elseif (\is_array($bootstrappers)) {
            $this->bootstrappers = [...$this->bootstrappers, ...$bootstrappers];
        }

        return $this;
    }
}
