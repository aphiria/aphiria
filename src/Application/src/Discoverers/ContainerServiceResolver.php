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

use Aphiria\Application\IApplicationBuilder;
use Aphiria\Application\IComponent;
use Aphiria\DependencyInjection\Container;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\IServiceResolver as DIIServiceResolver;
use Aphiria\DependencyInjection\ResolutionException;
use ReflectionClass;
use ReflectionException;
use ReflectionUnionType;
use RuntimeException;

/**
 * Defines the container resolver
 */
final class ContainerServiceResolver implements IServiceResolver
{
    /**
     * @param IContainer $container The service resolver to use
     * @param IApplicationBuilder $appBuilder The application builder to use
     */
    public function __construct(
        private readonly IContainer $container = new Container(),
        private readonly IApplicationBuilder $appBuilder,
    ) {}

    /**
     * @inheritdoc
     */
    public function resolve(string $className): object
    {
        try {
            $class = new ReflectionClass($className);

            if ($class->getConstructor()?->getNumberOfParameters() === 0) {
                return new $className();
            }

            $constructorParameters = [];

            foreach ($class->getConstructor()?->getParameters() as $parameter) {
                if ($parameter->getType() === null) {
                    throw new RuntimeException("Cannot resolve $className because parameter \$$parameter->name has no type");
                }

                if ($parameter->getType() instanceof ReflectionUnionType) {
                    throw new RuntimeException("Union types for parameter \$$parameter->name are not supported for $className");
                }

                $parameterClassName = $parameter->getType()->getName();

                if (\is_subclass_of($parameterClassName, IComponent::class)) {
                    if ($this->appBuilder->hasComponent($parameterClassName)) {
                        return $this->appBuilder->getComponent($parameterClassName);
                    }

                    $component = $this->container->resolve($parameterClassName);
                    $this->container->bindInstance($parameterClassName, $component);
                    $this->appBuilder->withComponent($component);
                    $constructorParameters[] = $component;

                    continue;
                }

                if (
                    \is_subclass_of($parameterClassName, IContainer::class)
                    || \is_subclass_of($parameterClassName, DIIServiceResolver::class)
                    || \is_subclass_of($parameterClassName, Container::class)
                ) {
                    $constructorParameters[] = $this->container;

                    continue;
                }

                if (\is_subclass_of($parameterClassName, IApplicationBuilder::class)) {
                    $constructorParameters[] = $this->appBuilder;

                    continue;
                }

                if (\is_subclass_of($parameterClassName, IServiceResolver::class)) {
                    $constructorParameters[] = $this;

                    continue;
                }

                throw new RuntimeException(
                    \sprintf(
                        "Cannot resolve $className::__construct($parameterClassName \$$parameter->name) - only the following types may be auto-resolved: %s",
                        \implode(', ', [IComponent::class, IContainer::class, DIIServiceResolver::class, Container::class, IApplicationBuilder::class, IServiceResolver::class]),
                    ),
                );
            }

            return new $className(...$constructorParameters);
        } catch (ResolutionException|ReflectionException $ex) {
            throw new RuntimeException("Could not resolve $className", 0, $ex);
        }
    }
}
