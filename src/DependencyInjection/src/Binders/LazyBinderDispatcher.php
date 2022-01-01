<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
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
use Aphiria\DependencyInjection\UniversalContext;
use Closure;

/**
 * Defines binder dispatcher that lazily dispatches binders only when their bindings are actually needed
 */
class LazyBinderDispatcher implements IBinderDispatcher
{
    /** @var array<class-string<Binder>, true> The list of already-dispatched binder classes */
    private array $alreadyDispatchedBinderClasses = [];

    /**
     * @param IBinderMetadataCollectionCache|null $binderMetadataCollectionCache The cache, if using one
     */
    public function __construct(private readonly ?IBinderMetadataCollectionCache $binderMetadataCollectionCache = null)
    {
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
        /** @var BinderMetadataCollection $binderMetadatas */
        foreach ($binderMetadatas->binderMetadatas as $binderMetadata) {
            foreach ($binderMetadata->boundInterfaces as $boundInterface) {
                $resolvingFactory = $this->createLazyFactory(
                    $binderMetadatas,
                    $binderMetadata,
                    $boundInterface,
                    $container
                );

                if ($boundInterface->context->isTargeted) {
                    $container->for(
                        $boundInterface->context,
                        fn (IContainer $container) => $container->bindFactory($boundInterface->interface, $resolvingFactory)
                    );
                } else {
                    $container->bindFactory($boundInterface->interface, $resolvingFactory);
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
     * @return Closure(): object The factory that can lazily resolve an interface
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
            if ($boundInterface->context->isTargeted) {
                $container->for(
                    $boundInterface->context,
                    fn (IContainer $container) => $container->unbind($boundInterface->interface)
                );
            } else {
                $container->unbind($boundInterface->interface);
            }

            $this->dispatchBinder($binderMetadata->binder, $container);
            $resolvedInterface = $this->resolveBoundInterface($boundInterface, $container);

            // Run any binders that need the resolved interface
            foreach ($binderMetadatas->getBinderMetadataThatResolveInterface($boundInterface) as $binderMetadata) {
                $this->dispatchBinder($binderMetadata->binder, $container);
            }

            return $resolvedInterface;
        };
    }

    /**
     * Dispatches a binder if it hasn't already been dispatched
     *
     * @param Binder $binder The binder to dispatch
     * @param IContainer $container The container to pass in
     */
    private function dispatchBinder(Binder $binder, IContainer $container): void
    {
        $key = $binder::class;

        // Make sure we don't double-dispatch this binder
        if (!isset($this->alreadyDispatchedBinderClasses[$key])) {
            /**
             * In the case that whatever invoked the lazy factory was a targeted binding/resolution, make sure that the
             * binder doesn't inherit that context, and instead is run in the universal context.
             */
            $container->for(new UniversalContext(), fn (IContainer $container) => $binder->bind($container));
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
        if ($boundInterface->context->isTargeted) {
            return $container->for(
                $boundInterface->context,
                fn (IContainer $container) => $container->resolve($boundInterface->interface)
            );
        }

        return $container->resolve($boundInterface->interface);
    }
}
