<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Binders\Metadata;

use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\IContainer;

/**
 * Defines a factory for binder metadata collections
 */
final class BinderMetadataCollectionFactory
{
    /** @var IBinderMetadataCollector The collector of binder metadata */
    private readonly IBinderMetadataCollector $binderMetadataCollector;

    /**
     * @param IContainer $container The container to use when creating the collection
     * @param IBinderMetadataCollector|null $binderMetadataCollector The collector of binder metadata
     */
    public function __construct(
        private readonly IContainer $container,
        ?IBinderMetadataCollector $binderMetadataCollector = null
    ) {
        $this->binderMetadataCollector = $binderMetadataCollector ?? new ContainerBinderMetadataCollector($this->container);
    }

    /**
     * Creates a binder metadata collection
     *
     * @param list<Binder> $binders The list of binders to create the metadata collection from
     * @return BinderMetadataCollection The created collection
     * @throws ImpossibleBindingException Thrown if the bindings are not possible to resolve
     */
    public function createBinderMetadataCollection(array $binders): BinderMetadataCollection
    {
        /** @var list<BinderMetadata> $binderMetadatas */
        $binderMetadatas = [];
        $failedInterfacesToBinders = [];

        foreach ($binders as $binder) {
            try {
                $binderMetadatas[] = $this->binderMetadataCollector->collect($binder);
            } catch (FailedBinderMetadataCollectionException $ex) {
                self::addFailedResolutionToMap($failedInterfacesToBinders, $ex);
            }

            $binderMetadatas = [
                ...$binderMetadatas,
                ...$this->retryFailedBinders($failedInterfacesToBinders)
            ];
        }

        if (\count($failedInterfacesToBinders) > 0) {
            throw new ImpossibleBindingException($failedInterfacesToBinders);
        }

        return new BinderMetadataCollection($binderMetadatas);
    }

    /**
     * Adds a failed resolution to a map
     *
     * @param array<class-string, list<Binder>> $failedInterfacesToBinders The map to add to
     * @param FailedBinderMetadataCollectionException $ex The exception that was thrown
     */
    private static function addFailedResolutionToMap(
        array &$failedInterfacesToBinders,
        FailedBinderMetadataCollectionException $ex
    ): void {
        $interface = $ex->failedInterface;

        if (!isset($failedInterfacesToBinders[$interface])) {
            $failedInterfacesToBinders[$interface] = [];
        }

        $failedInterfacesToBinders[$interface][] = $ex->incompleteBinderMetadata->binder;
    }

    /**
     * Removes a failed resolution from the map
     *
     * @param array<class-string, list<Binder>> $failedInterfacesToBinders The map to remove from
     * @param class-string $interface The interface to remove
     * @param int $binderIndex The index of the binder to remove
     */
    private static function removeFailedResolutionFromMap(
        array &$failedInterfacesToBinders,
        string $interface,
        int $binderIndex
    ): void {
        unset($failedInterfacesToBinders[$interface][$binderIndex]);

        // If this interface doesn't have any more failed binders, remove it
        if (\count($failedInterfacesToBinders[$interface]) === 0) {
            unset($failedInterfacesToBinders[$interface]);
        }
    }

    /**
     * Retries any failed resolutions
     *
     * @param array<class-string, list<Binder>> $failedInterfacesToBinders The map of failed resolutions to retry
     * @return list<BinderMetadata> The list of binder metadata that were retrieved from failed binders
     */
    private function retryFailedBinders(array &$failedInterfacesToBinders): array
    {
        $binderMetadatas = [];
        $fixedBinderClasses = [];

        foreach ($failedInterfacesToBinders as $interface => $binders) {
            if (!$this->container->hasBinding($interface)) {
                // No point in retrying if the container still cannot resolve the interface
                continue;
            }

            foreach ($binders as $i => $binder) {
                try {
                    // Don't double-retry a binder that has already been fixed
                    if (!isset($fixedBinderClasses[$binder::class])) {
                        $binderMetadatas[] = $this->binderMetadataCollector->collect($binder);
                        $fixedBinderClasses[$binder::class] = true;
                    }

                    // The binder must have been able to resolve everything, so make sure we don't retry it again
                    self::removeFailedResolutionFromMap($failedInterfacesToBinders, $interface, $i);
                } catch (FailedBinderMetadataCollectionException $ex) {
                    self::addFailedResolutionToMap($failedInterfacesToBinders, $ex);

                    // Remove any interfaces that did get resolved successfully prior to the exception
                    foreach ($ex->incompleteBinderMetadata->resolvedInterfaces as $resolvedInterface) {
                        // Check if this interface was successfully resolved before removing it from the map
                        if ($resolvedInterface->interface === $ex->failedInterface) {
                            continue;
                        }

                        self::removeFailedResolutionFromMap($failedInterfacesToBinders, $resolvedInterface->interface, $i);
                    }
                }
            }
        }

        return $binderMetadatas;
    }
}
