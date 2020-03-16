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
use Aphiria\DependencyInjection\IContainer;

/**
 * Defines a container that can be used to inspect the bindings set in a binder
 * @internal
 */
final class BindingInspectionContainer implements IContainer
{
    /** @var null The value of an empty target */
    private const EMPTY_TARGET = null;
    /** @var IContainer The underlying container that can resolve and bind instances */
    private IContainer $container;
    /** @var string|null The current target */
    private ?string $currentTarget = null;
    /** @var array The stack of targets */
    private array $targetStack = [];
    /** @var BinderBinding[] The binder bindings that were found */
    private array $binderBindings = [];
    /** @var Binder|null The current binder class */
    private ?Binder $currBinder = null;

    /**
     * @param IContainer $container The underlying container to use to resolve and bind instances
     */
    public function __construct(IContainer $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public function bindFactory($interfaces, callable $factory, bool $resolveAsSingleton = false): void
    {
        $this->addBinderBinding($interfaces);

        if ($this->currentTarget === null) {
            $this->container->bindFactory($interfaces, $factory, $resolveAsSingleton);
        } else {
            $this->container->for($this->currentTarget, fn (IContainer $container) => $container->bindFactory($interfaces, $factory, $resolveAsSingleton));
        }
    }

    /**
     * @inheritdoc
     */
    public function bindInstance($interfaces, object $instance): void
    {
        $this->addBinderBinding($interfaces);

        if ($this->currentTarget === null) {
            $this->container->bindInstance($interfaces, $instance);
        } else {
            $this->container->for($this->currentTarget, fn (IContainer $container) => $container->bindInstance($interfaces, $instance));
        }
    }

    /**
     * @inheritdoc
     */
    public function bindPrototype($interfaces, string $concreteClass = null, array $primitives = []): void
    {
        $this->addBinderBinding($interfaces);

        if ($this->currentTarget === null) {
            $this->container->bindPrototype($interfaces, $concreteClass, $primitives);
        } else {
            $this->container->for($this->currentTarget, fn (IContainer $container) => $container->bindPrototype($interfaces, $concreteClass, $primitives));
        }
    }

    /**
     * @inheritdoc
     */
    public function bindSingleton($interfaces, string $concreteClass = null, array $primitives = []): void
    {
        $this->addBinderBinding($interfaces);

        if ($this->currentTarget === null) {
            $this->container->bindSingleton($interfaces, $concreteClass, $primitives);
        } else {
            $this->container->for($this->currentTarget, fn (IContainer $container) => $container->bindSingleton($interfaces, $concreteClass, $primitives));
        }
    }

    /**
     * @inheritdoc
     */
    public function callClosure(callable $closure, array $primitives = [])
    {
        return $this->container->callClosure($closure, $primitives);
    }

    /**
     * @inheritdoc
     */
    public function callMethod($instance, string $methodName, array $primitives = [], bool $ignoreMissingMethod = false)
    {
        return $this->container->callMethod($instance, $methodName, $primitives, $ignoreMissingMethod);
    }

    /**
     * @inheritdoc
     */
    public function for(string $targetClass, callable $callback)
    {
        $this->currentTarget = $targetClass;
        $this->targetStack[] = $targetClass;

        $result = $callback($this);

        array_pop($this->targetStack);
        $this->currentTarget = end($this->targetStack) ?: self::EMPTY_TARGET;

        return $result;
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
            $binderBindings = [...$binderBindings, ...$bindings];
        }

        return $binderBindings;
    }

    /**
     * @inheritdoc
     */
    public function hasBinding(string $interface): bool
    {
        if ($this->currentTarget === null) {
            return $this->container->hasBinding($interface);
        }

        return $this->container->for($this->currentTarget, fn (IContainer $container) => $container->hasBinding($interface));
    }

    /**
     * @inheritdoc
     */
    public function resolve(string $interface): object
    {
        if ($this->currentTarget === null) {
            return $this->container->resolve($interface);
        }

        return $this->container->for($this->currentTarget, fn (IContainer $container) => $container->resolve($interface));
    }

    /**
     * Sets the current binding
     *
     * @param Binder $binder The current binding
     */
    public function setBinder(Binder $binder): void
    {
        $this->currBinder = $binder;
    }

    /**
     * @inheritdoc
     */
    public function tryResolve(string $interface, ?object &$instance): bool
    {
        if ($this->currentTarget === null) {
            return $this->container->tryResolve($interface, $instance);
        }

        return $this->container->for($this->currentTarget, fn (IContainer $container) => $container->tryResolve($interface, $instance));
    }

    /**
     * @inheritdoc
     */
    public function unbind($interfaces): void
    {
        if ($this->currentTarget === null) {
            $this->container->unbind($interfaces);
        }

        $this->container->for($this->currentTarget, fn (IContainer $container) => $container->unbind($interfaces));
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
