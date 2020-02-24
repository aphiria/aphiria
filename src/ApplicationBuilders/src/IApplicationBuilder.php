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

use Closure;
use InvalidArgumentException;
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
     * Configures a component builder by registering a callback that will manipulate it
     *
     * @param string $class The name of the component builder to call
     * @param Closure $callback The callback that will take an instance of the class param
     * @throws InvalidArgumentException Thrown if the component builder was not registered yet
     */
    public function configureComponentBuilder(string $class, Closure $callback): void;

    /**
     * Adds a component builder to the application
     *
     * @param string $class The name of the component builder class
     * @param Closure $factory The factory that will create the component builder
     * @param array $magicMethods The mapping of magic method names to callbacks
     * @return IApplicationBuilder For chaining
     * @throws InvalidArgumentException Thrown if the magic method was already registered
     */
    public function withComponentBuilder(string $class, Closure $factory, array $magicMethods = []): self;

    /**
     * Adds an entire module builder to the application
     *
     * @param IModuleBuilder $moduleBuilder The module builder to include
     * @return IApplicationBuilder For chaining
     */
    public function withModuleBuilder(IModuleBuilder $moduleBuilder): self;
}
