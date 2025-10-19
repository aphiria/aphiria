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

use Aphiria\DependencyInjection\IServiceResolver;
use Aphiria\DependencyInjection\ResolutionException;
use RuntimeException;

/**
 * Defines the container discoverer resolver
 */
final class ContainerDiscovererResolver implements IDiscovererResolver
{
    /**
     * @param IServiceResolver $serviceResolver The service resolver to use
     */
    public function __construct(private readonly IServiceResolver $serviceResolver) {}

    /**
     * @inheritdoc
     */
    public function resolve(string $discovererClassName): IComponentDiscoverer
    {
        try {
            return $this->serviceResolver->resolve($discovererClassName);
        } catch (ResolutionException $ex) {
            throw new RuntimeException('Could not resolve discoverer', 0, $ex);
        }
    }
}
