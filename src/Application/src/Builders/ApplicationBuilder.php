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
use OutOfBoundsException;

/**
 * Defines an application builder
 */
abstract class ApplicationBuilder implements IApplicationBuilder
{
    /** @var IBootstrapper[] The list of bootstrappers to run to bootstrap the application */
    private array $bootstrappers;
    /** @var IModuleBuilder[] The list of module builders */
    private array $moduleBuilders = [];
    /** @var IComponentBuilder[] The mapping of prioritized component builder names to builders */
    private array $componentBuildersByType = [];
    /** @var array The list of structs that contain component builder types and priorities */
    private array $componentBuilderTypesAndPriorities = [];

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
     * Bootstraps the application
     */
    protected function bootstrap(): void
    {
        foreach ($this->bootstrappers as $bootstrapper) {
            $bootstrapper->bootstrap();
        }
    }

    /**
     * Builds all the registered component builders
     */
    protected function buildComponents(): void
    {
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
