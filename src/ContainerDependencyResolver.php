<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/api/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api;

use Opulence\Ioc\IContainer;
use Opulence\Ioc\ResolutionException;

/**
 * Defines a dependency resolver that uses Opulence's DI container
 */
final class ContainerDependencyResolver implements IDependencyResolver
{
    /** @var IContainer The IoC container */
    private IContainer $container;

    /**
     * @param IContainer $container The IoC container
     */
    public function __construct(IContainer $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public function resolve(string $className): object
    {
        try {
            return $this->container->resolve($className);
        } catch (ResolutionException $ex) {
            throw new DependencyResolutionException(
                $ex->getInterface(),
                $ex->getTargetClass(),
                'Could not resolve dependencies',
                0,
                $ex
            );
        }
    }
}
