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
 * Defines a factory container binding
 * @internal
 */
class FactoryContainerBinding implements IContainerBinding
{
    /** @var callable(): object The factory */
    private $factory;

    /**
     * @param callable(): object $factory The factory
     * @param bool $resolveAsSingleton Whether or not the factory should be resolved as a singleton
     */
    public function __construct(callable $factory, private bool $resolveAsSingleton)
    {
        $this->factory = $factory;
    }

    /**
     * Gets the factory binding
     *
     * @return callable(): object The factory
     */
    public function getFactory(): callable
    {
        return $this->factory;
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
