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
 * Defines a factory container binding
 * @internal
 */
class FactoryContainerBinding implements IContainerBinding
{
    /** @var callable The factory */
    private $factory;

    /**
     * @param callable $factory The factory
     * @param bool $resolveAsSingleton Whether or not the factory should be resolved as a singleton
     */
    public function __construct(callable $factory, private bool $resolveAsSingleton)
    {
        $this->factory = $factory;
    }

    /**
     * @return callable
     */
    public function getFactory(): callable
    {
        return $this->factory;
    }

    /**
     * @return bool
     */
    public function resolveAsSingleton(): bool
    {
        return $this->resolveAsSingleton;
    }
}
