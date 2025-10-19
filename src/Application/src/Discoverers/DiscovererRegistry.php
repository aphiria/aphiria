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

use OutOfBoundsException;

/**
 * Defines the registry of discoverers
 */
final class DiscovererRegistry
{
    /** @var list<IComponentDiscoverer> The list of discoverers */
    public array $discoverers {
        get => \array_values($this->discovererTypesToDiscoverers);
    }
    /** @var array<class-string<IComponentDiscoverer>, IComponentBuilder> */
    private array $discovererTypesToBuilders = [];
    /** @var array<class-string<IComponentDiscoverer>, IComponentDiscoverer> */
    private array $discovererTypesToDiscoverers = [];

    /**
     * Gets the builder for a discoverer type
     *
     * @param class-string<IComponentDiscoverer> $type The type of the discoverer whose builder to get
     * @return IComponentBuilder The builder for the discoverer type
     * @throws OutOfBoundsException Thrown if there was no builder registered for the discoverer type
     */
    public function getBuilder(string $type): IComponentBuilder
    {
        return $this->discovererTypesToBuilders[$type]
            ?? throw new OutOfBoundsException("No component builder registered for type $type");
    }

    /**
     * @param IComponentDiscoverer $discoverer The discoverer to register
     * @param IComponentBuilder $builder The builder for discovered components
     */
    public function registerDiscoverer(IComponentDiscoverer $discoverer, IComponentBuilder $builder): void
    {
        $this->discovererTypesToDiscoverers[$discoverer::class] = $discoverer;
        $this->discovererTypesToBuilders[$discoverer::class] = $builder;
    }
}
