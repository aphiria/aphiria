<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Discoverers;

use Aphiria\Application\IComponent;
use Aphiria\DependencyInjection\IContainer;

/**
 * Defines the discoverer component
 *
 * TODO:  Move this into the framework library
 */
class DiscovererComponent implements IComponent
{
    /** @var bool Whether or not discoverers are enabled */
    private bool $discoverersEnabled = false;
    /** @var string|null The path to scan for discoverers in, or null if not enabled */
    private ?string $path = null;

    /**
     * @param IContainer $container The container to use
     */
    public function __construct(private readonly IContainer $container) {}

    /**
     * @inheritdoc
     */
    public function build(): void
    {
        if (!$this->discoverersEnabled || $this->path === null) {
            return;
        }

        $discoveredComponentBuilder = new DiscoveredComponentBuilder($this->path, new ContainerResolver($this->container));
        $discoveredComponentBuilder->build();
    }

    /**
     * Enables discoverers
     *
     * @return static For chaining
     */
    public function withDiscoverers(string $path): static
    {
        $this->discoverersEnabled = true;
        $this->path = $path;

        return $this;
    }
}
