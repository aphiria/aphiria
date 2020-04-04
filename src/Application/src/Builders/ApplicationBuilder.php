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

use Aphiria\Application\IComponent;
use Aphiria\Application\IModule;
use OutOfBoundsException;

/**
 * Defines an application builder
 */
abstract class ApplicationBuilder implements IApplicationBuilder
{
    /** @var IModule[] The list of modules */
    private array $modules = [];
    /** @var IComponent[] The mapping of prioritized component names to components */
    private array $componentsByType = [];
    /** @var array The list of structs that contain component types and priorities */
    private array $componentTypesAndPriorities = [];

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
     * Builds all the registered components
     */
    protected function buildComponents(): void
    {
        \usort($this->componentTypesAndPriorities, fn ($a, $b) => $a['priority'] <=> $b['priority']);

        foreach ($this->componentTypesAndPriorities as $typeAndPriority) {
            $this->componentsByType[$typeAndPriority['type']]->build();
        }
    }

    /**
     * Builds all the registered modules
     */
    protected function buildModules(): void
    {
        // Modules might be registered inside of modules, so we use a for loop instead of foreach (keys are changing)
        for ($i = 0;$i < \count($this->modules);$i++) {
            $this->modules[$i]->build($this);
        }
    }
}
