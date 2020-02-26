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

/**
 * Defines an application builder
 */
abstract class ApplicationBuilder implements IApplicationBuilder
{
    /** @var IModuleBuilder[] The list of module builders */
    private array $moduleBuilders = [];
    /** @var IComponentBuilder[] The mapping of prioritized component builder names to builders */
    private array $componentBuildersByType = [];
    /** @var array The list of structs that contain component builder types and priorities */
    private array $componentBuilderTypesAndPriorities = [];

    /**
     * @inheritdoc
     */
    public function getComponentBuilder(string $type): IComponentBuilder
    {
        if (!isset($this->componentBuildersByType[$type])) {
            throw new OutOfBoundsException("No component builder of type $type found");
        }

        return $this->componentBuildersByType[$type];
    }

    /**
     * @inheritdoc
     */
    public function hasComponentBuilder(string $type): bool
    {
        return isset($this->componentBuildersByType[$type]);
    }

    /**
     * @inheritdoc
     */
    public function withComponentBuilder(IComponentBuilder $componentBuilder, int $priority = null): IApplicationBuilder
    {
        $type = $componentBuilder instanceof IComponentBuilderProxy ? $componentBuilder->getProxiedType() : \get_class($componentBuilder);
        $this->componentBuilderTypesAndPriorities[] = ['type' => $type, 'priority' => $priority ?? \PHP_INT_MAX];
        $this->componentBuildersByType[$type] = $componentBuilder;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withModuleBuilder(IModuleBuilder $moduleBuilder): IApplicationBuilder
    {
        $this->moduleBuilders[] = $moduleBuilder;

        return $this;
    }

    /**
     * Builds all the registered component builders
     */
    protected function buildComponents(): void
    {
        // TODO: Need to verify this orders in things properly
        \usort($this->componentBuilderTypesAndPriorities, fn ($a, $b) => $a['priority'] <=> $b['priority']);

        foreach ($this->componentBuilderTypesAndPriorities as $typeAndPriority) {
            $this->componentBuildersByType[$typeAndPriority['type']]->build($this);
        }
    }

    /**
     * Builds all the registered module builders
     */
    protected function buildModules(): void
    {
        foreach ($this->moduleBuilders as $moduleBuilder) {
            $moduleBuilder->build($this);
        }
    }
}
