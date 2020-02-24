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

/**
 * Defines an application builder
 */
abstract class ApplicationBuilder implements IApplicationBuilder
{
    /** @var IComponentBuilder[] The mapping of component builder names to builders */
    protected array $componentBuilderFactories = [];
    /** @var Closure[] The mapping of component builder names to the enqueued list of component builder calls */
    protected array $componentBuilderCalls = [];

    /**
     * @inheritdoc
     */
    public function configureComponentBuilder(string $class, Closure $callback): void
    {
        if (!isset($this->componentBuilderFactories[$class])) {
            throw new InvalidArgumentException("No component builder of type $class is registered");
        }

        if (!isset($this->componentBuilderCalls[$class])) {
            $this->componentBuilderCalls[$class] = [];
        }

        $this->componentBuilderCalls[$class][] = $callback;
    }

    /**
     * @inheritdoc
     */
    public function withComponentBuilder(string $class, Closure $factory, array $magicMethods = []): IApplicationBuilder
    {
        $this->componentBuilderFactories[$class] = $factory;

        foreach ($magicMethods as $magicMethodName => $callback) {
            if (isset($this->componentMagicMethodsToCallbacks[$magicMethodName])) {
                throw new InvalidArgumentException("Magic method $magicMethodName is already registered");
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withModuleBuilder(IModuleBuilder $moduleBuilder): IApplicationBuilder
    {
        $moduleBuilder->build($this);

        return $this;
    }

    /**
     * Builds all the registered component builders
     */
    protected function buildComponents(): void
    {
        foreach ($this->componentBuilderFactories as $componentBuilderName => $componentBuilderFactory) {
            /** @var IComponentBuilder $componentBuilder */
            $componentBuilder = $componentBuilderFactory();

            // Calls to component builders should happen before they're built
            if (isset($this->componentBuilderCalls[$componentBuilderName])) {
                foreach ($this->componentBuilderCalls[$componentBuilderName] as $componentBuilderCallback) {
                    $componentBuilderCallback($componentBuilder);
                }
            }

            $componentBuilder->build($this);
        }
    }
}
