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

use Aphiria\Application\Discoverers\ContainerResolver;
use Aphiria\Application\Discoverers\DiscoveredComponentBuilder;
use Aphiria\Application\IComponent;
use Aphiria\DependencyInjection\IServiceResolver;

/**
 * Defines the discoverer component
 */
class DiscovererComponent implements IComponent
{
    /** @var bool Whether or not discoverers are enabled */
    private bool $discoverersEnabled = false;
    /** @var string|null The path to scan for discoverers in, or null if not enabled */
    private ?string $path = null;

    /**
     * @param IServiceResolver $serviceResolver The service resolver to use
     */
    public function __construct(private readonly IServiceResolver $serviceResolver) {}

    /**
     * @inheritdoc
     */
    public function build(): void
    {
        if (!$this->discoverersEnabled || $this->path === null) {
            return;
        }

        $discoveredComponentBuilder = new DiscoveredComponentBuilder($this->path, new ContainerResolver($this->serviceResolver));
        $discoveredComponentBuilder->build();
    }

    /**
     * Enables discoverers
     *
     * @param string $path The path to scan for discoverers in
     * @return static For chaining
     */
    public function withDiscoverers(string $path): static
    {
        $this->discoverersEnabled = true;
        $this->path = $path;

        return $this;
    }
}
