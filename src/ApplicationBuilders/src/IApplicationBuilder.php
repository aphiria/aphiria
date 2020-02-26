<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ApplicationBuilders;

use OutOfBoundsException;
use RuntimeException;

/**
 * Defines the interface for applications builders to implement
 */
interface IApplicationBuilder
{
    /**
     * Builds an application
     *
     * @return object The built application
     * @throws RuntimeException Thrown if there was an error building the application
     */
    public function build(): object;

    /**
     * Gets a component builder by its type (if it's a lazy component builder, then we use ILazyComponentBuilder::getType())
     *
     * @param string $type The type of component builder to get
     * @return IComponentBuilder The component builder, if one was found
     * @throws OutOfBoundsException Thrown if there was no component builder with that type
     */
    public function getComponentBuilder(string $type): IComponentBuilder;

    /**
     * Gets whether or not the application builder has a registered instance of the input component builder type
     *
     * @param string $type The type of component builder to check for
     * @return bool True if the application builder already has the component builder, otherwise false
     */
    public function hasComponentBuilder(string $type): bool;

    /**
     * Adds a component builder to the application
     *
     * @param IComponentBuilder $componentBuilder The component builder to register
     * @param int|null $priority The optional priority of this component builder (lower number => higher priority)
     * @return self For chaining
     */
    public function withComponentBuilder(IComponentBuilder $componentBuilder, int $priority = null): self;

    /**
     * Adds an entire module builder to the application
     *
     * @param IModuleBuilder $moduleBuilder The module builder to register
     * @return self For chaining
     */
    public function withModuleBuilder(IModuleBuilder $moduleBuilder): self;
}
