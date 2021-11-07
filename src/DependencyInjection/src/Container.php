<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;

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
    /** @var Context The current context */
    protected Context $currentContext;
    /** @var list<Context> The stack of contexts */
    protected array $contextStack = [];
    /** @var array<string|class-string, array<string|class-string, IContainerBinding>> The list of bindings */
    protected array $bindings = [];
    /** @var array<class-string, array{0: ReflectionMethod|null, 1: list<ReflectionParameter>|null}> The cache of reflection constructors and their parameters */
    protected array $constructorReflectionCache = [];

    public function __construct()
    {
        // Default to a universal context
        $this->currentContext = new UniversalContext();
    }

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
            $this->currentContext = $globalInstance->currentContext;
            $this->contextStack = $globalInstance->contextStack;
            $this->bindings = $globalInstance->bindings;
            $this->constructorReflectionCache = $globalInstance->constructorReflectionCache;
        } else {
            $this->currentContext = new UniversalContext();
            $this->contextStack = $this->bindings = $this->constructorReflectionCache = [];
        }
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
        $binding = new ClassContainerBinding($concreteClass, $primitives, $resolveAsSingleton);

        foreach ((array)$interfaces as $interface) {
            $this->addBinding($interface, $binding);
        }
    }

    /**
     * @inheritdoc
     */
    public function bindFactory(string|array $interfaces, callable $factory, bool $resolveAsSingleton = false): void
    {
        $binding = new FactoryContainerBinding($factory, $resolveAsSingleton);

        foreach ((array)$interfaces as $interface) {
            $this->addBinding($interface, $binding);
        }
    }

    /**
     * @inheritdoc
     *
     * @psalm-suppress MoreSpecificImplementedParamType Instance will always be an instance of interface(s) - bug
     * @psalm-suppress MixedArgumentTypeCoercion Template types are not passed through via inheritdoc - bug
     */
    public function bindInstance(string|array $interfaces, object $instance): void
    {
        $binding = new InstanceContainerBinding($instance);

        foreach ((array)$interfaces as $interface) {
            $this->addBinding($interface, $binding);
        }
    }

    /**
     * @inheritdoc
     */
    public function callClosure(Closure $closure, array $primitives = []): mixed
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
    public function callMethod(object|string $instance, string $methodName, array $primitives = [], bool $ignoreMissingMethod = false): mixed
    {
        $className = \is_string($instance) ? $instance : $instance::class;

        if (!\method_exists($instance, $methodName)) {
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
    public function for(Context|string $context, callable $callback)
    {
        if (\is_string($context)) {
            $context = new TargetedContext($context);
        }

        // We're duplicating the tracking of targets here so that we can know if any bindings are targeted or universal
        $this->currentContext = $context;
        $this->contextStack[] = $context;
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
        if (
            $this->currentContext->isTargeted()
            && $this->hasTargetedBinding($interface, $this->currentContext->getTargetClass())
        ) {
            return true;
        }

        return $this->hasTargetedBinding($interface);
    }

    /**
     * @inheritdoc
     *
     * @psalm-suppress InvalidReturnType This method will always return the correct type
     * @psalm-suppress InvalidReturnStatement Ditto
     */
    public function resolve(string $interface): object
    {
        $binding = $this->getBinding($interface);

        if ($binding === null) {
            // Try just resolving this directly
            return $this->resolveClass($interface);
        }

        switch ($binding::class) {
            case InstanceContainerBinding::class:
                /** @var InstanceContainerBinding $binding */
                return $binding->getInstance();
            case ClassContainerBinding::class:
                $instance = $this->resolveClass(
                    $binding->getConcreteClass(),
                    $binding->getConstructorPrimitives()
                );
                break;
            case FactoryContainerBinding::class:
                $factory = $binding->getFactory();
                $instance = $factory();
                break;
            default:
                throw new ResolutionException($interface, $this->currentContext, 'Invalid binding type "' . $binding::class . '"');
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
    public function tryResolve(string $interface, ?object &$instance): bool
    {
        try {
            $instance = $this->resolve($interface);

            return true;
        } catch (ResolutionException $ex) {
            $instance = null;

            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function unbind(string|array $interfaces): void
    {
        $target = $this->currentContext->getTargetClass() ?? '';

        foreach ((array)$interfaces as $interface) {
            unset($this->bindings[$target][$interface]);
        }
    }

    /**
     * Adds a binding to an interface
     *
     * @param class-string $interface The interface to bind to
     * @param IContainerBinding $binding The binding to add
     */
    protected function addBinding(string $interface, IContainerBinding $binding): void
    {
        $target = $this->currentContext->getTargetClass() ?? '';

        if (!isset($this->bindings[$target])) {
            $this->bindings[$target] = [];
        }

        $this->bindings[$target][$interface] = $binding;
    }

    /**
     * Gets a binding for an interface
     *
     * @param class-string $interface The interface whose binding we want
     * @return IContainerBinding|null The binding if one exists, otherwise null
     */
    protected function getBinding(string $interface): ?IContainerBinding
    {
        // If there's a targeted binding, use it
        if (
            $this->currentContext->isTargeted()
            && isset($this->bindings[($targetClass = $this->currentContext->getTargetClass())][$interface])
        ) {
            /** @var class-string $targetClass This will not be null because the context is targeted */
            return $this->bindings[$targetClass][$interface];
        }

        // If there's a universal binding, use it
        return $this->bindings[''][$interface] ?? null;
    }

    /**
     * Gets whether or not a targeted binding exists
     *
     * @param class-string $interface The interface to check
     * @param class-string|null $target The target whose bindings we're checking
     * @return bool True if the targeted binding exists, otherwise false
     */
    protected function hasTargetedBinding(string $interface, string $target = null): bool
    {
        return isset($this->bindings[$target][$interface]);
    }

    /**
     * Resolves a class
     *
     * @template T
     * @param class-string<T> $className The class name to resolve
     * @param list<mixed> $primitives The list of constructor primitives
     * @return T The resolved class
     * @throws ResolutionException Thrown if the class could not be resolved
     */
    protected function resolveClass(string $className, array $primitives = []): object
    {
        try {
            if (isset($this->constructorReflectionCache[$className])) {
                [$constructor, $parameters] = $this->constructorReflectionCache[$className];
            } else {
                $reflectionClass = new ReflectionClass($className);

                if (!$reflectionClass->isInstantiable()) {
                    throw new ResolutionException(
                        $className,
                        $this->currentContext,
                        \sprintf(
                            '%s is not instantiable%s',
                            $className,
                            $this->currentContext->isTargeted() ? " (dependency of {$this->currentContext->getTargetClass()})" : ''
                        )
                    );
                }

                $constructor = $reflectionClass->getConstructor();
                $parameters = $constructor !== null ? $constructor->getParameters() : null;
                $this->constructorReflectionCache[$className] = [$constructor, $parameters];
            }

            if ($constructor === null) {
                // No constructor, so instantiating is easy
                /** @psalm-suppress MixedMethodCall We're purposely instantiating this class */
                return new $className();
            }

            $constructorParameters = $this->resolveParameters($className, $parameters ?? [], $primitives);

            /** @psalm-suppress MixedMethodCall We're purposely instantiating this class */
            return new $className(...$constructorParameters);
        } catch (ReflectionException $ex) {
            throw new ResolutionException($className, $this->currentContext, "Failed to resolve class $className", 0, $ex);
        }
    }

    /**
     * Resolves a list of parameters for a function call
     *
     * @param class-string|null $className The name of the class whose parameters we're resolving
     * @param list<ReflectionParameter> $unresolvedParameters The list of unresolved parameters
     * @param list<mixed> $primitives The list of primitive values
     * @return list<mixed> The list of parameters with all the dependencies resolved
     * @throws ResolutionException Thrown if there was an error resolving the parameters
     * @throws ReflectionException Thrown if there was a reflection exception
     */
    protected function resolveParameters(
        ?string $className,
        array $unresolvedParameters,
        array $primitives
    ): array {
        $resolvedParameters = [];

        foreach ($unresolvedParameters as $parameter) {
            $parameterResolved = false;
            $parameterType = $parameter->getType();
            /** @var list<ReflectionNamedType|null> $parameterTypes */
            $parameterTypes = $parameterType instanceof ReflectionUnionType ? $parameterType->getTypes() : [$parameterType];

            foreach ($parameterTypes as $parameterType) {
                try {
                    $parameterClassName = $parameterType !== null && !$parameterType->isBuiltin()
                        ? $parameterType->getName()
                        : null;

                    if ($parameterClassName === null) {
                        // The parameter is a primitive
                        /** @psalm-suppress MixedAssignment The resolved parameter will be a mixed type */
                        $resolvedParameter = $this->resolvePrimitive($parameter, $parameterType, $primitives);
                    } elseif ($className !== null && $this->hasTargetedBinding($parameterClassName, $className)) {
                        $resolvedParameter = $this->for(
                            new TargetedContext($className),
                            fn (IContainer $container) => $container->resolve($parameterClassName)
                        );
                    } else {
                        try {
                            $resolvedParameter = $this->resolve($parameterClassName);
                        } catch (ResolutionException $ex) {
                            // Check for a default value
                            if ($parameter->isDefaultValueAvailable()) {
                                /** @psalm-suppress MixedAssignment The resolved parameter will be a mixed type */
                                $resolvedParameter = $parameter->getDefaultValue();
                            } elseif ($parameter->allowsNull()) {
                                $resolvedParameter = null;
                            } else {
                                throw $ex;
                            }
                        }
                    }

                    /** @psalm-suppress MixedAssignment The resolved parameter will be a mixed type */
                    $resolvedParameters[] = $resolvedParameter;
                    $parameterResolved = true;
                } catch (ResolutionException) {
                    // Continue
                }
            }

            if (!$parameterResolved) {
                /** @psalm-suppress ArgumentTypeCoercion We're OK with the slight edge case that the class name was null here */
                throw new ResolutionException(
                    $className ?? '',
                    $this->currentContext,
                    \sprintf(
                        'Failed to resolve %s in %s::%s()',
                        $parameter->getName(),
                        $parameter->getDeclaringClass()?->getName() ?? 'Unknown',
                        $parameter->getDeclaringFunction()->getName()
                    )
                );
            }
        }

        return $resolvedParameters;
    }

    /**
     * Resolves a primitive parameter
     *
     * @param ReflectionParameter $parameter The primitive parameter to resolve
     * @param ReflectionType|null $reflectionType The type to resolve the primitive as, or null if there was no type
     * @param array $primitives The list of primitive values
     * @return mixed The resolved primitive
     * @throws ResolutionException Thrown if there was an error resolving the primitive
     * @psalm-suppress ArgumentTypeCoercion We're suppressing the fact that the parameter type might be a non-class-string to simplify things
     */
    protected function resolvePrimitive(ReflectionParameter $parameter, ?ReflectionType $reflectionType, array &$primitives): mixed
    {
        $parameterTypeName = $reflectionType instanceof \ReflectionNamedType ? $reflectionType->getName() : (string)$reflectionType;

        if (\count($primitives) > 0) {
            // Grab the next primitive, and make sure its type matches the type of the next parameter if it has a type
            if (
                $reflectionType !== null
                && $parameterTypeName !== 'mixed'
            ) {
                $primitiveTypeName = \gettype($primitives[0]);

                if ($primitiveTypeName !== $parameterTypeName) {
                    throw new ResolutionException(
                        $parameterTypeName,
                        $this->currentContext,
                        \sprintf(
                            'Expected type %s, got %s for %s in %s::%s()',
                            $parameterTypeName,
                            $primitiveTypeName,
                            $parameter->getName(),
                            $parameter->getDeclaringClass()?->getName() ?? 'Unknown',
                            $parameter->getDeclaringFunction()->getName()
                        )
                    );
                }
            }

            return \array_shift($primitives);
        }

        if ($parameter->isDefaultValueAvailable()) {
            // No value was found, so use the default value
            try {
                return $parameter->getDefaultValue();
                // @codeCoverageIgnoreStart
            } catch (ReflectionException $ex) {
                throw new ResolutionException(
                    $parameterTypeName,
                    $this->currentContext,
                    "Failed to get the default value for parameter {$parameter->getName()}",
                    0,
                    $ex
                );
                // @codeCoverageIgnoreEnd
            }
        }

        throw new ResolutionException(
            $parameterTypeName,
            $this->currentContext,
            \sprintf(
                'No default value available for %s in %s::%s()',
                $parameter->getName(),
                $parameter->getDeclaringClass()?->getName() ?? 'Unknown',
                $parameter->getDeclaringFunction()->getName()
            )
        );
    }
}
