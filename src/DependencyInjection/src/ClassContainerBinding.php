<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
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
     * @param string $concreteClass The name of the concrete class
     * @param array $constructorPrimitives The list of constructor primitives
     * @param bool $resolveAsSingleton Whether or not to resolve as a singleton
     */
    public function __construct(
        private string $concreteClass,
        private array $constructorPrimitives,
        private bool $resolveAsSingleton
    ) {
    }

    /**
     * @return string
     */
    public function getConcreteClass(): string
    {
        return $this->concreteClass;
    }

    /**
     * @return array
     */
    public function getConstructorPrimitives(): array
    {
        return $this->constructorPrimitives;
    }

    /**
     * @return bool
     */
    public function resolveAsSingleton(): bool
    {
        return $this->resolveAsSingleton;
    }
}
