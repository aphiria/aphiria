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

use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;

/**
 * Defines the dependency injection container
 */
class Container implements IContainer
{
    /**
     * The global instance of the container to use, or null if not set.
     * This is especially useful when deserializing a container - we cannot deserialize one directly.  But, you can
     * use the global instance's bindings to set $this->bindings on __wakeup.  It's recommended that this be set
     * to the instance of the container that you use throughout your application if you rely on serializing the container.
     *
     * @var Container|null
     */
    public static ?Container $globalInstance = null;
    /** @var null The value of an empty target */
    private const EMPTY_TARGET = null;
    /** @var string|null The current target */
    protected ?string $currentTarget = null;
    /** @var array The stack of targets */
    protected array $targetStack = [];
    /** @var IContainerBinding[][] The list of bindings */
    protected array $bindings = [];
    /** @var array The cache of reflection constructors and their parameters */
    protected array $constructorReflectionCache = [];

    /**
     * Prepares the container for serialization
     */
    public function __sleep(): array
    {
        return [];
    }

    /**
     * Since a container's bindings cannot actually be serialized (too complicated/expensive), we can try to set the
     * bindings from the global instance's bindings, instead.
     */
    public function __wakeup()
    {
        if (($globalInstance = self::$globalInstance) !== null) {
            $this->currentTarget = $globalInstance->currentTarget;
            $this->targetStack = $globalInstance->targetStack;
            $this->bindings = $globalInstance->bindings;
            $this->constructorReflectionCache = $globalInstance->constructorReflectionCache;
        }
    }

    /**
     * @inheritdoc
     */
    public function bindFactory($interfaces, callable $factory, bool $resolveAsSingleton = false): void
    {
        $binding = new FactoryContainerBinding($factory, $resolveAsSingleton);

        foreach ((array)$interfaces as $interface) {
            $this->addBinding($interface, $binding);
        }
    }

    /**
     * @inheritdoc
     */
    public function bindInstance($interfaces, object $instance): void
    {
        $binding = new InstanceContainerBinding($instance);

        foreach ((array)$interfaces as $interface) {
            $this->addBinding($interface, $binding);
        }
    }

    /**
     * @inheritdoc
     */
    public function bindPrototype($interfaces, string $concreteClass = null, array $primitives = []): void
    {
        foreach ((array)$interfaces as $interface) {
            $this->addBinding($interface, new ClassContainerBinding($concreteClass ?? $interface, $primitives, false));
        }
    }

    /**
     * @inheritdoc
     */
    public function bindSingleton($interfaces, string $concreteClass = null, array $primitives = []): void
    {
        foreach ((array)$interfaces as $interface) {
            $this->addBinding($interface, new ClassContainerBinding($concreteClass ?? $interface, $primitives, true));
        }
    }

    /**
     * @inheritdoc
     */
    public function callClosure(callable $closure, array $primitives = [])
    {
        try {
            $unresolvedParameters = (new ReflectionFunction($closure))->getParameters();
            $resolvedParameters = $this->resolveParameters(null, $unresolvedParameters, $primitives);

            return $closure(...$resolvedParameters);
        } catch (ReflectionException | ResolutionException $ex) {
            throw new CallException('Failed to call closure', 0, $ex);
        }
    }

    /**
     * @inheritdoc
     */
    public function callMethod($instance, string $methodName, array $primitives = [], bool $ignoreMissingMethod = false)
    {
        $className = is_string($instance) ? $instance : get_class($instance);

        if (!method_exists($instance, $methodName)) {
            if (!$ignoreMissingMethod) {
                throw new CallException("Method $className::$methodName does not exist");
            }

            return null;
        }

        try {
            $unresolvedParameters = (new ReflectionMethod($instance, $methodName))->getParameters();
            $resolvedParameters = $this->resolveParameters($className, $unresolvedParameters, $primitives);

            return ([$instance, $methodName])(...$resolvedParameters);
        } catch (ReflectionException | ResolutionException $ex) {
            throw new CallException("Failed to call method $className::$methodName", 0, $ex);
        }
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
     * @inheritdoc
     */
    public function hasBinding(string $interface): bool
    {
        if ($this->currentTarget !== self::EMPTY_TARGET
            && $this->hasTargetedBinding($interface, $this->currentTarget)
        ) {
            return true;
        }

        return $this->hasTargetedBinding($interface, self::EMPTY_TARGET);
    }

    /**
     * @inheritdoc
     */
    public function resolve(string $interface): object
    {
        $binding = $this->getBinding($interface);

        if ($binding === null) {
            // Try just resolving this directly
            return $this->resolveClass($interface);
        }

        switch (get_class($binding)) {
            case InstanceContainerBinding::class:
                /** @var InstanceContainerBinding $binding */
                return $binding->getInstance();
            case ClassContainerBinding::class:
                /** @var ClassContainerBinding $binding */
                $instance = $this->resolveClass(
                    $binding->getConcreteClass(),
                    $binding->getConstructorPrimitives()
                );
                break;
            case FactoryContainerBinding::class:
                /** @var FactoryContainerBinding $binding */
                $factory = $binding->getFactory();
                $instance = $factory();
                break;
            default:
                throw new ResolutionException($interface, $this->currentTarget, 'Invalid binding type "' . get_class($binding) . '"');
        }

        if ($binding->resolveAsSingleton()) {
            $this->unbind($interface);
            $this->addBinding($interface, new InstanceContainerBinding($instance));
        }

        return $instance;
    }

    /**
     * @inheritdoc
     */
    public function tryResolve(string $interface, &$instance): bool
    {
        try {
            $instance = $this->resolve($interface);

            return true;
        } catch (ResolutionException $ex) {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function unbind($interfaces): void
    {
        foreach ((array)$interfaces as $interface) {
            unset($this->bindings[$this->currentTarget][$interface]);
        }
    }

    /**
     * Adds a binding to an interface
     *
     * @param string $interface The interface to bind to
     * @param IContainerBinding $binding The binding to add
     */
    protected function addBinding(string $interface, IContainerBinding $binding): void
    {
        if (!isset($this->bindings[$this->currentTarget])) {
            $this->bindings[$this->currentTarget] = [];
        }

        $this->bindings[$this->currentTarget][$interface] = $binding;
    }

    /**
     * Gets a binding for an interface
     *
     * @param string $interface The interface whose binding we want
     * @return IContainerBinding|null The binding if one exists, otherwise null
     */
    protected function getBinding(string $interface): ?IContainerBinding
    {
        // If there's a targeted binding, use it
        if ($this->currentTarget !== self::EMPTY_TARGET && isset($this->bindings[$this->currentTarget][$interface])) {
            return $this->bindings[$this->currentTarget][$interface];
        }

        // If there's a universal binding, use it
        return $this->bindings[self::EMPTY_TARGET][$interface] ?? null;
    }

    /**
     * Gets whether or not a targeted binding exists
     *
     * @param string $interface The interface to check
     * @param string|null $target The target whose bindings we're checking
     * @return bool True if the targeted binding exists, otherwise false
     */
    protected function hasTargetedBinding(string $interface, string $target = null): bool
    {
        return isset($this->bindings[$target][$interface]);
    }

    /**
     * Resolves a class
     *
     * @param string $class The class name to resolve
     * @param array $primitives The list of constructor primitives
     * @return object The resolved class
     * @throws ResolutionException Thrown if the class could not be resolved
     */
    protected function resolveClass(string $class, array $primitives = []): object
    {
        try {
            if (isset($this->constructorReflectionCache[$class])) {
                [$constructor, $parameters] = $this->constructorReflectionCache[$class];
            } else {
                $reflectionClass = new ReflectionClass($class);

                if (!$reflectionClass->isInstantiable()) {
                    throw new ResolutionException(
                        $class,
                        $this->currentTarget,
                        sprintf(
                            '%s is not instantiable%s',
                            $class,
                            $this->currentTarget === null ? '' : " (dependency of {$this->currentTarget})"
                        )
                    );
                }

                $constructor = $reflectionClass->getConstructor();
                $parameters = $constructor !== null ? $constructor->getParameters() : null;
                $this->constructorReflectionCache[$class] = [$constructor, $parameters];
            }

            if ($constructor === null) {
                // No constructor, so instantiating is easy
                return new $class;
            }

            $constructorParameters = $this->resolveParameters($class, $parameters, $primitives);

            return new $class(...$constructorParameters);
        } catch (ReflectionException $ex) {
            throw new ResolutionException($class, $this->currentTarget, "Failed to resolve class $class", 0, $ex);
        }
    }

    /**
     * Resolves a list of parameters for a function call
     *
     * @param string|null $class The name of the class whose parameters we're resolving
     * @param ReflectionParameter[] $unresolvedParameters The list of unresolved parameters
     * @param array $primitives The list of primitive values
     * @return array The list of parameters with all the dependencies resolved
     * @throws ResolutionException Thrown if there was an error resolving the parameters
     * @throws ReflectionException Thrown if there was a reflection exception
     */
    protected function resolveParameters(
        $class,
        array $unresolvedParameters,
        array $primitives
    ): array {
        $resolvedParameters = [];

        foreach ($unresolvedParameters as $parameter) {
            $resolvedParameter = null;

            if ($parameter->getClass() === null) {
                // The parameter is a primitive
                $resolvedParameter = $this->resolvePrimitive($parameter, $primitives);
            } else {
                // The parameter is an object
                $parameterClassName = $parameter->getClass()->getName();

                /**
                 * We need to first check if the input class is a target for the parameter
                 * If it is, resolve it using the input class as a target
                 * Otherwise, attempt to resolve it universally
                 */
                if ($class !== null && $this->hasTargetedBinding($parameterClassName, $class)) {
                    $resolvedParameter = $this->for(
                        $class,
                        fn (IContainer $container) => $container->resolve($parameter->getClass()->getName())
                    );
                } else {
                    try {
                        $resolvedParameter = $this->resolve($parameterClassName);
                    } catch (ResolutionException $ex) {
                        // Check for a default value
                        if ($parameter->isDefaultValueAvailable()) {
                            $resolvedParameter = $parameter->getDefaultValue();
                        } elseif ($parameter->allowsNull()) {
                            $resolvedParameter = null;
                        } else {
                            throw $ex;
                        }
                    }
                }
            }

            $resolvedParameters[] = $resolvedParameter;
        }

        return $resolvedParameters;
    }

    /**
     * Resolves a primitive parameter
     *
     * @param ReflectionParameter $parameter The primitive parameter to resolve
     * @param array $primitives The list of primitive values
     * @return mixed The resolved primitive
     * @throws ReflectionException Thrown if there was a reflection exception
     */
    protected function resolvePrimitive(ReflectionParameter $parameter, array &$primitives)
    {
        if (count($primitives) > 0) {
            // Grab the next primitive
            return array_shift($primitives);
        }

        if ($parameter->isDefaultValueAvailable()) {
            // No value was found, so use the default value
            return $parameter->getDefaultValue();
        }

        throw new ReflectionException(
            sprintf(
                'No default value available for %s in %s::%s()',
                $parameter->getName(),
                $parameter->getDeclaringClass()->getName(),
                $parameter->getDeclaringFunction()->getName()
            )
        );
    }
}
