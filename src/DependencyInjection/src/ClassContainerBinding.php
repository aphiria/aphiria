<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection;

/**
 * Defines a class container binding
 * @template T of object
 * @implements IContainerBinding<T>
 * @internal
 */
readonly class ClassContainerBinding implements IContainerBinding
{
    /**
     * @param class-string<T> $concreteClass The name of the concrete class
     * @param list<mixed> $constructorPrimitives The list of constructor primitives
     * @param bool $resolveAsSingleton Whether or not to resolve as a singleton
     */
    public function __construct(
        public string $concreteClass,
        public array $constructorPrimitives,
        private bool $resolveAsSingleton
    ) {
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
