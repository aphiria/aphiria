<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Binders\Metadata;

use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\Context;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\ResolutionException;
use Aphiria\DependencyInjection\TargetedContext;
use Aphiria\DependencyInjection\UniversalContext;
use InvalidArgumentException;

/**
 * Defines what a collector of metadata about a binder
 */
final class ContainerBinderMetadataCollector implements IBinderMetadataCollector, IContainer
{
    /** @var IContainer The underlying container that can resolve and bind instances */
    private IContainer $container;
    /** @var Context The current context */
    private Context $currentContext;
    /** @var Context[] The stack of contexts */
    private array $contextStack = [];
    /** @var BoundInterface[] The list of bound interfaces that were found */
    private array $boundInterfaces = [];
    /** @var ResolvedInterface[] The list of resolved interfaces that were found */
    private array $resolvedInterfaces = [];

    /**
     * @param IContainer $container The underlying container to use to resolve and bind instances
     */
    public function __construct(IContainer $container)
    {
        $this->container = $container;
        // Default to a universal context
        $this->currentContext = new UniversalContext();
    }

    /**
     * @inheritdoc
     */
    public function bindClass(
        $interfaces,
        string $concreteClass,
        array $primitives = [],
        bool $resolveAsSingleton = false
    ): void {
        $this->addBoundInterface($interfaces);

        if ($this->currentContext === null) {
            $this->container->bindClass($interfaces, $concreteClass, $primitives, $resolveAsSingleton);
        } else {
            $this->container->for(
                $this->currentContext,
                fn (IContainer $container) => $container->bindClass($interfaces, $concreteClass, $primitives, $resolveAsSingleton)
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function bindFactory($interfaces, callable $factory, bool $resolveAsSingleton = false): void
    {
        $this->addBoundInterface($interfaces);

        if ($this->currentContext === null) {
            $this->container->bindFactory($interfaces, $factory, $resolveAsSingleton);
        } else {
            $this->container->for($this->currentContext, fn (IContainer $container) => $container->bindFactory($interfaces, $factory, $resolveAsSingleton));
        }
    }

    /**
     * @inheritdoc
     */
    public function bindInstance($interfaces, object $instance): void
    {
        $this->addBoundInterface($interfaces);

        if ($this->currentContext === null) {
            $this->container->bindInstance($interfaces, $instance);
        } else {
            $this->container->for($this->currentContext, fn (IContainer $container) => $container->bindInstance($interfaces, $instance));
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
    public function collect(Binder $binder): BinderMetadata
    {
        try {
            $binder->bind($this);

            return new BinderMetadata($binder, $this->boundInterfaces, $this->resolvedInterfaces);
        } catch (ResolutionException $ex) {
            $incompleteBinderMetadata = new BinderMetadata($binder, $this->boundInterfaces, $this->resolvedInterfaces);

            throw new FailedBinderMetadataCollectionException($incompleteBinderMetadata, $ex->getInterface(), 0, $ex);
        } finally {
            // Reset for next time
            $this->boundInterfaces = $this->resolvedInterfaces = $this->contextStack = [];
            $this->currentContext = new UniversalContext();
        }
    }

    /**
     * @inheritdoc
     */
    public function for($context, callable $callback)
    {
        if (\is_string($context)) {
            $context = new TargetedContext($context);
        }

        if (!$context instanceof Context) {
            throw new InvalidArgumentException('Context must be an instance of ' . Context::class . ' or string');
        }

        $this->currentContext = $context;
        $this->contextStack[] = $context;

        $result = $callback($this);

        array_pop($this->contextStack);
        $this->currentContext = end($this->contextStack) ?: new UniversalContext();

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function hasBinding(string $interface): bool
    {
        if ($this->currentContext === null) {
            return $this->container->hasBinding($interface);
        }

        return $this->container->for($this->currentContext, fn (IContainer $container) => $container->hasBinding($interface));
    }

    /**
     * @inheritdoc
     */
    public function resolve(string $interface): object
    {
        $this->addResolvedInterface($interface);

        return $this->container->for($this->currentContext, fn (IContainer $container) => $container->resolve($interface));
    }

    /**
     * @inheritdoc
     */
    public function tryResolve(string $interface, ?object &$instance): bool
    {
        $this->addResolvedInterface($interface);

        return $this->container->for($this->currentContext, fn(IContainer $container) => $container->tryResolve($interface, $instance));
    }

    /**
     * @inheritdoc
     */
    public function unbind($interfaces): void
    {
        if ($this->currentContext === null) {
            $this->container->unbind($interfaces);
        }

        $this->container->for($this->currentContext, fn (IContainer $container) => $container->unbind($interfaces));
    }

    /**
     * Adds a bound interface to the list of bound interfaces
     *
     * @param string[]|string $interfaces The interface or interfaces we're binding
     */
    private function addBoundInterface($interfaces): void
    {
        foreach ((array)$interfaces as $interface) {
            $boundInterface = new BoundInterface($interface, $this->currentContext);

            // We do not want to double-add bound interfaces (a universal and targeted binding are considered different)
            if (!\in_array($boundInterface, $this->boundInterfaces, false)) {
                $this->boundInterfaces[] = $boundInterface;
            }
        }
    }

    /**
     * Adds a resolved interface to the list of resolved interfaces
     *
     * @param string $interface The interface we're resolving
     */
    private function addResolvedInterface(string $interface): void
    {
        $resolvedInterface = new ResolvedInterface($interface, $this->currentContext);

        // We do not want to double-add resolved interfaces (a universal and targeted binding are considered different)
        if (!\in_array($resolvedInterface, $this->resolvedInterfaces, false)) {
            $this->resolvedInterfaces[] = $resolvedInterface;
        }
    }
}
