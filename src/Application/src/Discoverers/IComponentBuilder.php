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
 * Defines the interface for component builders to implement
 */
interface IComponentBuilder
{
    /** @var class-string<IComponentDiscoverer> The name of the discoverer class whose components we're building */
    public string $discovererClassName { get; }

    /**
     * @param list<DiscoveredComponent> $components The discovered components to build
     */
    public function build(array $components): void;
}
