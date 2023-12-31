<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application;

use OutOfBoundsException;

/**
 * Defines an application builder
 */
abstract class ApplicationBuilder implements IApplicationBuilder
{
    /** @var array<class-string<IComponent>, IComponent> The mapping of prioritized component names to components */
    private array $componentsByType = [];
    /** @var array<array{type: class-string<IComponent>, priority: int}> The list of structs that contain component types and priorities */
    private array $componentTypesAndPriorities = [];
    /** @var list<IModule> The list of modules */
    private array $modules = [];

    /**
     * @inheritdoc
     * @template T of IComponent
     * @param class-string<T> $type The type of component to get
     * @return T The component, if one was found
     * @psalm-suppress InvalidReturnType This does return type T - bug
     */
    public function getComponent(string $type): IComponent
    {
        if (!isset($this->componentsByType[$type])) {
            throw new OutOfBoundsException("No component of type $type found");
        }

        /** @psalm-suppress InvalidReturnStatement We're purposely avoiding using class-string-map due to its poor IDE support */
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
    public function withComponent(IComponent $component, int $priority = null): static
    {
        $type = $component::class;
        $this->componentTypesAndPriorities[] = ['type' => $type, 'priority' => $priority ?? \PHP_INT_MAX];
        $this->componentsByType[$type] = $component;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withModule(IModule $module): static
    {
        $this->modules[] = $module;

        return $this;
    }

    /**
     * Builds all the registered components
     */
    protected function buildComponents(): void
    {
        \usort($this->componentTypesAndPriorities, static fn (array $a, array $b): int => $a['priority'] <=> $b['priority']);

        foreach ($this->componentTypesAndPriorities as $typeAndPriority) {
            $this->componentsByType[$typeAndPriority['type']]->build();
        }
    }

    /**
     * Configures all the registered modules
     */
    protected function configureModules(): void
    {
        // Modules might be registered inside of modules, so we use a for loop instead of foreach (keys are changing)
        for ($i = 0;$i < \count($this->modules);$i++) {
            $this->modules[$i]->configure($this);
        }
    }
}
