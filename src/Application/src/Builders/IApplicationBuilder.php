<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Builders;

use Aphiria\Application\IApplication;
use Aphiria\Application\IComponent;
use Aphiria\Application\IModule;
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
     * @return IApplication The built application
     * @throws RuntimeException Thrown if there was an error building the application
     */
    public function build(): IApplication;

    /**
     * Gets a component by its type
     *
     * @template T of IComponent
     * @param class-string<T> $type The type of component to get
     * @return T The component, if one was found
     * @throws OutOfBoundsException Thrown if there was no component with that type
     */
    public function getComponent(string $type): IComponent;

    /**
     * Gets whether or not the application builder has a registered instance of the input component type
     *
     * @param class-string<IComponent> $type The type of component to check for
     * @return bool True if the application builder already has the component, otherwise false
     */
    public function hasComponent(string $type): bool;

    /**
     * Adds a component to the application
     *
     * @param IComponent $component The component to register
     * @param int|null $priority The optional priority of this component (lower number => higher priority)
     * @return static For chaining
     */
    public function withComponent(IComponent $component, int $priority = null): static;

    /**
     * Adds an entire module to the application
     *
     * @param IModule $module The module to register
     * @return static For chaining
     */
    public function withModule(IModule $module): static;
}
