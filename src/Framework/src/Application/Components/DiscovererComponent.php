<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Application\Components;

use Aphiria\Application\Discoverers\Caching\IDiscoveredComponentCache;
use Aphiria\Application\Discoverers\ContainerServiceResolver;
use Aphiria\Application\Discoverers\DiscoveredComponentBuilder;
use Aphiria\Application\IApplicationBuilder;
use Aphiria\Application\IComponent;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\ResolutionException;
use RuntimeException;

/**
 * Defines the discoverer component
 */
class DiscovererComponent implements IComponent
{
    /** @var IDiscoveredComponentCache|null The optional cache for discovered components */
    private ?IDiscoveredComponentCache $cache = null;
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

        try {
            $discoveredComponentBuilder = new DiscoveredComponentBuilder(
                $this->path,
                new ContainerServiceResolver($this->container->resolve(IApplicationBuilder::class), $this->container),
                $this->cache,
            );
        } catch (ResolutionException $ex) {
            throw new RuntimeException('Failed to resolve ' . IApplicationBuilder::class, 0, $ex);
        }

        $discoveredComponentBuilder->build();
    }

    /**
     * Enables discoverers
     *
     * @param string $path The path to scan for discoverers in
     * @param IDiscoveredComponentCache|null $cache The optional cache for discovered components
     * @return static For chaining
     */
    public function withDiscoverers(string $path, ?IDiscoveredComponentCache $cache = null): static
    {
        $this->discoverersEnabled = true;
        $this->path = $path;
        $this->cache = $cache;

        return $this;
    }
}
