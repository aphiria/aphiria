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

/**
 * TODO: Describe this, and think about whether this is really a collection vs a registry
 */
class DiscoveredComponentCollection
{
    /** @var array<class-string<IComponentDiscoverer>, list<DiscoveredComponent>> The list of discovered components by discoverer type */
    private array $componentsByDiscoverer = [];

    /**
     * Adds a discovered component to the collection
     *
     * @param class-string<IComponentDiscoverer> $type The type of component being added
     * @param DiscoveredComponent $component The component to add
     */
    public function add(string $type, DiscoveredComponent $component): void
    {
        if (!isset($this->componentsByDiscoverer[$type])) {
            $this->componentsByDiscoverer[$type] = [];
        }

        $this->componentsByDiscoverer[$type][] = $component;
    }

    /**
     * Gets all discovered components in the collection
     *
     * @return list<DiscoveredComponent> The list of discovered components by type
     */
    public function getAll(): array
    {
        return $this->componentsByDiscoverer;
    }
}
