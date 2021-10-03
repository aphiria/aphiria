<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Binders\Metadata;

use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\Context;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\ResolutionException;
use Aphiria\DependencyInjection\TargetedContext;
use Aphiria\DependencyInjection\UniversalContext;
use Closure;

/**
 * Defines what a collector of metadata about a binder
 */
final class ContainerBinderMetadataCollector implements IBinderMetadataCollector, IContainer
{
    /** @var Context The current context */
    private Context $currentContext;
    /** @var list<Context> The stack of contexts */
    private array $contextStack = [];
    /** @var list<BoundInterface> The list of bound interfaces that were found */
    private array $boundInterfaces = [];
    /** @var list<ResolvedInterface> The list of resolved interfaces that were found */
    private array $resolvedInterfaces = [];

    /**
     * @param IContainer $container The underlying container to use to resolve and bind instances
     */
    public function __construct(private IContainer $container)
    {
        // Default to a universal context
        $this->currentContext = new UniversalContext();
    }

    /**
     * @inheritdoc
     */
    public function bindClass(
        string|array $interfaces,
        string $concreteClass,
        array $primitives = [],
        bool $resolveAsSingleton = false
    ): void {
        $this->addBoundInterface($interfaces);
        $this->container->for(
            $this->currentContext,
            fn (IContainer $container) => $container->bindClass($interfaces, $concreteClass, $primitives, $resolveAsSingleton)
        );
    }

    /**
     * @inheritdoc
     */
    public function bindFactory(string|array $interfaces, callable $factory, bool $resolveAsSingleton = false): void
    {
        $this->addBoundInterface($interfaces);
        $this->container->for($this->currentContext, fn (IContainer $container) => $container->bindFactory($interfaces, $factory, $resolveAsSingleton));
    }

    /**
     * @inheritdoc
     *
     * @psalm-suppress MoreSpecificImplementedParamType Instance will always be an instance of interface(s) - bug
     */
    public function bindInstance(string|array $interfaces, object $instance): void
    {
        $this->addBoundInterface($interfaces);
        $this->container->for($this->currentContext, fn (IContainer $container) => $container->bindInstance($interfaces, $instance));
    }

    /**
     * @inheritdoc
     */
    public function callClosure(Closure $closure, array $primitives = []): mixed
    {
        return $this->container->callClosure($closure, $primitives);
    }

    /**
     * @inheritdoc
     */
    public function callMethod(object|string $instance, string $methodName, array $primitives = [], bool $ignoreMissingMethod = false): mixed
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

            throw new FailedBinderMetadataCollectionException($incompleteBinderMetadata, $ex->interface, 0, $ex);
        } finally {
            // Reset for next time
            $this->boundInterfaces = $this->resolvedInterfaces = $this->contextStack = [];
            $this->currentContext = new UniversalContext();
        }
    }

    /**
     * @inheritdoc
     */
    public function for(Context|string $context, callable $callback)
    {
        if (\is_string($context)) {
            $context = new TargetedContext($context);
        }

        $this->currentContext = $context;
        $this->contextStack[] = $context;

        /** @psalm-suppress ArgumentTypeCoercion The callback will accept $this - bug */
        $result = $callback($this);

        \array_pop($this->contextStack);
        $this->currentContext = \end($this->contextStack) ?: new UniversalContext();

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function hasBinding(string $interface): bool
    {
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

        // Use a long closure so that we can pass the instance in by reference
        return $this->container->for($this->currentContext, function (IContainer $container) use ($interface, &$instance) {
            /** @psalm-suppress MixedArgument Psalm does not handle by-reference params in lambdas (#4507) - bug */
            return $container->tryResolve($interface, $instance);
        });
    }

    /**
     * @inheritdoc
     */
    public function unbind(string|array $interfaces): void
    {
        $this->container->for($this->currentContext, fn (IContainer $container) => $container->unbind($interfaces));
    }

    /**
     * Adds a bound interface to the list of bound interfaces
     *
     * @param array<class-string>|class-string $interfaces The interface or interfaces we're binding
     */
    private function addBoundInterface(string|array $interfaces): void
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
     * @param class-string $interface The interface we're resolving
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
