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

use RuntimeException;

/**
 * Defines the interface for discoverer resolvers to implement
 */
interface IDiscovererResolver
{
    /**
     * Resolves a discoverer
     *
     * @template TDiscoverer of IComponentDiscoverer
     * @param class-string<TDiscoverer> $discovererClassName The fully-qualified name of the discoverer to resolve
     * @return TDiscoverer The resolved discoverer
     * @throws RuntimeException Thrown if the discoverer could not be resolved
     */
    public function resolve(string $discovererClassName): IComponentDiscoverer;
}
