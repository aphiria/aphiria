<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Builders;

use Aphiria\Application\IBootstrapper;
use Aphiria\Application\IComponent;
use Aphiria\Application\IModule;
use OutOfBoundsException;

/**
 * Defines an application builder
 */
abstract class ApplicationBuilder implements IApplicationBuilder
{
    /** @var IBootstrapper[] The list of bootstrappers to run to bootstrap the application */
    private array $bootstrappers;
    /** @var IModule[] The list of modules */
    private array $modules = [];
    /** @var IComponent[] The mapping of prioritized component names to components */
    private array $componentsByType = [];
    /** @var array The list of structs that contain component types and priorities */
    private array $componentTypesAndPriorities = [];

    /**
     * @param IBootstrapper[] $bootstrappers The list of bootstrappers to run to bootstrap the application
     */
    protected function __construct(array $bootstrappers)
    {
        $this->bootstrappers = $bootstrappers;
    }

    /**
     * @inheritdoc
     */
    public function getComponent(string $type): IComponent
    {
        if (!isset($this->componentsByType[$type])) {
            throw new OutOfBoundsException("No component of type $type found");
        }

        return $this->componentsByType[$type];
    }

    /**
     * @inheritdoc
     */
    public function hasComponent(string $type): bool
    {
        return isset($this->componentsByType[$type]);
    }

    /**
     * @inheritdoc
     */
    public function withComponent(IComponent $component, int $priority = null): IApplicationBuilder
    {
        $type = \get_class($component);
        $this->componentTypesAndPriorities[] = ['type' => $type, 'priority' => $priority ?? \PHP_INT_MAX];
        $this->componentsByType[$type] = $component;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withModule(IModule $module): IApplicationBuilder
    {
        $this->modules[] = $module;

        return $this;
    }

    /**
     * Bootstraps the application
     */
    protected function bootstrap(): void
    {
        foreach ($this->bootstrappers as $bootstrapper) {
            $bootstrapper->bootstrap();
        }
    }

    /**
     * Builds all the registered module builders
     */
    protected function buildModules(): void
    {
        foreach ($this->modules as $moduleBuilder) {
            $moduleBuilder->build($this);
        }
    }

    /**
     * Builds all the registered components
     */
    protected function buildComponents(): void
    {
        \usort($this->componentTypesAndPriorities, fn ($a, $b) => $a['priority'] <=> $b['priority']);

        foreach ($this->componentTypesAndPriorities as $typeAndPriority) {
            $this->componentsByType[$typeAndPriority['type']]->build();
        }
    }
}
