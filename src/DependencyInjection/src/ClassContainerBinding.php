<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection;

/**
 * Defines a class container binding
 * @internal
 */
class ClassContainerBinding implements IContainerBinding
{
    /**
     * @param class-string $concreteClass The name of the concrete class
     * @param list<mixed> $constructorPrimitives The list of constructor primitives
     * @param bool $resolveAsSingleton Whether or not to resolve as a singleton
     */
    public function __construct(
        private string $concreteClass,
        private array $constructorPrimitives,
        private bool $resolveAsSingleton
    ) {
    }

    /**
     * Gets the concrete class
     *
     * @return class-string The concrete class
     */
    public function getConcreteClass(): string
    {
        return $this->concreteClass;
    }

    /**
     * Gets the list of constructor primitives
     *
     * @return list<mixed> The list of constructor primitives
     */
    public function getConstructorPrimitives(): array
    {
        return $this->constructorPrimitives;
    }

    /**
     * Gets whether or not to resolve as a singleton
     *
     * @return bool Whether or not to resolve as a singleton
     */
    public function resolveAsSingleton(): bool
    {
        return $this->resolveAsSingleton;
    }
}
