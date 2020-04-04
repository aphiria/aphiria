<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection;

/**
 * Defines a binding registry
 */
final class BindingRegistry
{
    /** @var IContainerBinding[][] The mapping of targets to interfaces to bindings */
    private array $targetedBindings = [];
    /** @var IContainerBinding[] The mapping of interfaces to universal bindings */
    private array $universalBindings = [];

    /**
     * Gets a binding for an interface
     *
     * @param string $interface The interface whose binding we want
     * @param Context $context The current context
     * @return IContainerBinding|null The binding if one exists, otherwise null
     */
    public function getBinding(string $interface, Context $context): ?IContainerBinding
    {
        // If there's a targeted binding, use it
        if ($context->isTargeted() && isset($this->targetedBindings[$context->getTargetClass()][$interface])) {
            return $this->targetedBindings[$context->getTargetClass()][$interface];
        }

        // If there's a universal binding, use it
        return $this->universalBindings[$interface] ?? null;
    }

    /**
     * Gets whether or not an interface has a binding
     *
     * @param string $interface The interface to check
     * @param Context $context The current context
     * @return bool True if the interface has a binding, otherwise false
     */
    public function hasBinding(string $interface, Context $context): bool
    {
        if ($context->isTargeted() && isset($this->targetedBindings[$context->getTargetClass()][$interface])) {
            return true;
        }

        return isset($this->universalBindings[$interface]);
    }

    /**
     * Registers a binding to the registry
     *
     * @param string $interface The interface whose binding we're adding
     * @param IContainerBinding $binding The binding to register
     * @param Context $context The current context
     */
    public function registerBinding(string $interface, IContainerBinding $binding, Context $context): void
    {
        if ($context->isTargeted()) {
            $target = $context->getTargetClass();

            if (!isset($this->targetedBindings[$target])) {
                $this->targetedBindings[$target] = [];
            }

            $this->targetedBindings[$target][$interface] = $binding;
        } else {
            $this->universalBindings[$interface] = $binding;
        }
    }

    /**
     * Removes a binding from the registry
     *
     * @param string $interface The interface to unbind from
     * @param Context $context The current context
     */
    public function removeBinding(string $interface, Context $context): void
    {
        if ($context->isTargeted()) {
            unset($this->targetedBindings[$context->getTargetClass()][$interface]);
        } else {
            unset($this->universalBindings[$interface]);
        }
    }
}
