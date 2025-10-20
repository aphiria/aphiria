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

use Aphiria\DependencyInjection\Container;
use Aphiria\DependencyInjection\IServiceResolver;
use Aphiria\DependencyInjection\ResolutionException;
use RuntimeException;

/**
 * Defines the container resolver
 */
final class ContainerResolver implements IResolver
{
    /**
     * @param IServiceResolver $serviceResolver The service resolver to use
     */
    public function __construct(private readonly IServiceResolver $serviceResolver = new Container()) {}

    /**
     * @inheritdoc
     */
    public function resolve(string $className): object
    {
        try {
            return $this->serviceResolver->resolve($className);
        } catch (ResolutionException $ex) {
            throw new RuntimeException("Could not resolve $className", 0, $ex);
        }
    }
}
