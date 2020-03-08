<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Binders\Inspection;

use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\Container;

/**
 * Defines a container that can be used to inspect the bindings set in a binder
 * @internal
 */
final class BindingInspectionContainer extends Container
{
    /** @var BinderBinding[] The binder bindings that were found */
    private array $binderBindings = [];
    /** @var Binder|null The current binder class */
    private ?Binder $currBinder = null;

    /**
     * @inheritdoc
     */
    public function bindFactory($interfaces, callable $factory, bool $resolveAsSingleton = false): void
    {
        $this->addBinderBinding($interfaces);
        parent::bindFactory($interfaces, $factory, $resolveAsSingleton);
    }

    /**
     * @inheritdoc
     */
    public function bindInstance($interfaces, object $instance): void
    {
        $this->addBinderBinding($interfaces);
        parent::bindInstance($interfaces, $instance);
    }

    /**
     * @inheritdoc
     */
    public function bindPrototype($interfaces, string $concreteClass = null, array $primitives = []): void
    {
        $this->addBinderBinding($interfaces);
        parent::bindPrototype($interfaces, $concreteClass, $primitives);
    }

    /**
     * @inheritdoc
     */
    public function bindSingleton($interfaces, string $concreteClass = null, array $primitives = []): void
    {
        $this->addBinderBinding($interfaces);
        parent::bindSingleton($interfaces, $concreteClass, $primitives);
    }

    /**
     * Gets all the bindings that were found
     *
     * @return BinderBinding[] The bindings that were found
     */
    public function getBindings(): array
    {
        // We don't want the keys returned
        $binderBindings = [];

        foreach ($this->binderBindings as $interface => $bindings) {
            $binderBindings = \array_merge($binderBindings, $bindings);
        }

        return $binderBindings;
    }

    public function setBinder(Binder $binder): void
    {
        $this->currBinder = $binder;
    }

    /**
     * Adds a binding to the container if it does not already exist
     *
     * @param array|string $interfaces The interface or interfaces we're registering a binding for
     */
    private function addBinderBinding($interfaces): void
    {
        foreach ((array)$interfaces as $interface) {
            $binderBinding = $this->createBinderBinding($interface);
            $isTargetedBinding = $binderBinding instanceof TargetedBinderBinding;

            if (!isset($this->binderBindings[$interface])) {
                $this->binderBindings[$interface] = [$binderBinding];
                continue;
            }

            // Check if this exact binding has already been registered
            $bindingAlreadyExists = false;

            /** @var BinderBinding $existingBinderBinding */
            foreach ($this->binderBindings[$interface] as $existingBinderBinding) {
                if (
                    $binderBinding->getInterface() !== $existingBinderBinding->getInterface()
                    || $binderBinding->getBinder() !== $existingBinderBinding->getBinder()
                ) {
                    continue;
                }

                if ($isTargetedBinding) {
                    if ($existingBinderBinding instanceof TargetedBinderBinding) {
                        $bindingAlreadyExists = true;
                        break;
                    }
                } elseif ($existingBinderBinding instanceof UniversalBinderBinding) {
                    $bindingAlreadyExists = true;
                    break;
                }
            }

            if (!$bindingAlreadyExists) {
                $this->binderBindings[$interface][] = $binderBinding;
            }
        }
    }

    /**
     * Creates an inspection binding
     *
     * @param string $interface The interface that was bound
     * @return BinderBinding The binding for the interface
     */
    private function createBinderBinding(string $interface): BinderBinding
    {
        return $this->currentTarget === null
            ? new UniversalBinderBinding($interface, $this->currBinder)
            : new TargetedBinderBinding($this->currentTarget, $interface, $this->currBinder);
    }
}
