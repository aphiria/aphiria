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
    protected array $moduleBuilders = [];
    /** @var IComponentBuilder[] The mapping of component builder names to builders */
    protected array $componentBuilders = [];

    /**
     * @inheritdoc
     */
    public function getComponentBuilder(string $type): IComponentBuilder
    {
        if (!$this->hasComponentBuilder($type)) {
            throw new OutOfBoundsException("No component builder of type $type found");
        }

        return $this->componentBuilders[$type];
    }

    /**
     * @inheritdoc
     */
    public function hasComponentBuilder(string $type): bool
    {
        return isset($this->componentBuilders[$type]);
    }

    /**
     * @inheritdoc
     */
    public function withComponentBuilder(IComponentBuilder $componentBuilder): IApplicationBuilder
    {
        if ($componentBuilder instanceof IComponentBuilderProxy) {
            $this->componentBuilders[$componentBuilder->getProxiedType()] = $componentBuilder;
        } else {
            $this->componentBuilders[\get_class($componentBuilder)] = $componentBuilder;

        }

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
        foreach ($this->componentBuilders as $componentBuilderName => $componentBuilder) {
            $componentBuilder->build($this);
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
