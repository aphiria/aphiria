<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Binders;

use Aphiria\DependencyInjection\Binders\Metadata\BinderMetadata;
use Aphiria\DependencyInjection\Binders\Metadata\BinderMetadataCollection;
use Aphiria\DependencyInjection\Binders\Metadata\BinderMetadataCollectionFactory;
use Aphiria\DependencyInjection\Binders\Metadata\BoundInterface;
use Aphiria\DependencyInjection\Binders\Metadata\Caching\IBinderMetadataCollectionCache;
use Aphiria\DependencyInjection\Binders\Metadata\ImpossibleBindingException;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\ResolutionException;
use Closure;

/**
 * Defines binder dispatcher that lazily dispatches binders only when their bindings are actually needed
 */
class LazyBinderDispatcher implements IBinderDispatcher
{
    /** @var IBinderMetadataCollectionCache|null The cache for the binder metadata collection, or null if not caching */
    private ?IBinderMetadataCollectionCache $binderMetadataCollectionCache;
    /** @var string[] The list of already-dispatched binder classes */
    private array $alreadyDispatchedBinderClasses = [];

    /**
     * @param IBinderMetadataCollectionCache|null $binderMetadataCollectionCache The cache, if using one
     */
    public function __construct(IBinderMetadataCollectionCache $binderMetadataCollectionCache = null)
    {
        $this->binderMetadataCollectionCache = $binderMetadataCollectionCache;
    }

    /**
     * @inheritdoc
     * @throws ImpossibleBindingException Thrown if there was an unresolvable binder
     */
    public function dispatch(array $binders, IContainer $container): void
    {
        $binderMetadatasFactory = new BinderMetadataCollectionFactory($container);

        if ($this->binderMetadataCollectionCache === null) {
            $binderMetadatas = $binderMetadatasFactory->createBinderMetadataCollection($binders);
        } elseif (($binderMetadatas = $this->binderMetadataCollectionCache->get()) === null) {
            $binderMetadatas = $binderMetadatasFactory->createBinderMetadataCollection($binders);
            $this->binderMetadataCollectionCache->set($binderMetadatas);
        }

        // Create a bunch of factories to lazily resolve interfaces only when they're needed
        foreach ($binderMetadatas->getAllBinderMetadata() as $binderMetadata) {
            foreach ($binderMetadata->getBoundInterfaces() as $boundInterface) {
                $resolvingFactory = $this->createLazyFactory(
                    $binderMetadatas,
                    $binderMetadata,
                    $boundInterface,
                    $container
                );

                if ($boundInterface->isTargeted()) {
                    $container->for(
                        $boundInterface->getTargetClass(),
                        fn (IContainer $container) => $container->bindFactory($boundInterface->getInterface(), $resolvingFactory)
                    );
                } else {
                    $container->bindFactory($boundInterface->getInterface(), $resolvingFactory);
                }
            }
        }
    }

    /**
     * Creates a parameterless factory that can be used to lazily resolve an interface
     *
     * @param BinderMetadataCollection $binderMetadatas The collection of all binder metadata
     * @param BinderMetadata $binderMetadata The metadata for the binder that bound the interface
     * @param BoundInterface $boundInterface The bound interface
     * @param IContainer $container The DI container to register to
     * @return Closure The factory that can lazily resolve an interface
     */
    private function createLazyFactory(
        BinderMetadataCollection $binderMetadatas,
        BinderMetadata $binderMetadata,
        BoundInterface $boundInterface,
        IContainer $container
    ): Closure {
        return function () use ($binderMetadatas, $binderMetadata, $boundInterface, $container) {
            /**
             * To make sure this factory isn't used anymore to resolve the bound interface, unbind it and rely on the
             * binding defined in the binder.  Otherwise, we'd get into an infinite loop every time we tried
             * to resolve it.
             */
            if ($boundInterface->isTargeted()) {
                $container->for(
                    $boundInterface->getTargetClass(),
                    fn (IContainer $container) => $container->unbind($boundInterface->getInterface())
                );
            } else {
                $container->unbind($boundInterface->getInterface());
            }

            $this->dispatchBinder($binderMetadata->getBinder(), $container);
            $resolvedInterface = $this->resolveBoundInterface($boundInterface, $container);

            // Run any binders that need the resolved interface
            foreach ($binderMetadatas->getBinderMetadataThatResolveInterface($boundInterface) as $binderMetadata) {
                $this->dispatchBinder($binderMetadata->getBinder(), $container);
            }

            return $resolvedInterface;
        };
    }

    /**
     * Dispatches a binder if it hasn't already been dispatched
     *
     * @param Binder $binder The binder to dispatch
     * @param IContainer $container The container to pass in
     * @throws ResolutionException Thrown if a dependency could not be resolved
     */
    private function dispatchBinder(Binder $binder, IContainer $container): void
    {
        $key = \get_class($binder);

        // Make sure we don't double-dispatch this binder
        if (!isset($this->alreadyDispatchedBinderClasses[$key])) {
            $binder->bind($container);
            $this->alreadyDispatchedBinderClasses[$key] = true;
        }
    }

    /**
     * Resolves a bound interface
     *
     * @param BoundInterface $boundInterface The bound interface to resolve
     * @param IContainer $container The container to use to resolve the interface
     * @return object The resolved interface
     * @throws ResolutionException Thrown if the interface could not be resolved
     */
    private function resolveBoundInterface(BoundInterface $boundInterface, IContainer $container): object
    {
        if ($boundInterface->isTargeted()) {
            return $container->for(
                $boundInterface->getTargetClass(),
                fn (IContainer $container) => $container->resolve($boundInterface->getInterface())
            );
        }

        return $container->resolve($boundInterface->getInterface());
    }
}
