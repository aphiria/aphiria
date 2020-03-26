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
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\ResolutionException;

/**
 * Defines a factory for binder metadata collections
 */
final class BinderMetadataCollectionFactory
{
    /** @var IContainer The container to use when creating the collection */
    private IContainer $container;

    /**
     * @param IContainer $container The container to use when creating the collection
     */
    public function __construct(IContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Creates a binder metadata collection
     *
     * @param Binder[] The list of binders to create the metadata collection from
     * @return BinderMetadataCollection The created collection
     * @throws ImpossibleBindingException Thrown if the bindings are not possible to resolve
     */
    public function createBinderMetadataCollection(array $binders): BinderMetadataCollection
    {
        $binderMetadatas = [];
        $failedInterfacesToBinders = [];

        foreach ($binders as $binder) {
            try {
                $binderMetadatas[] = $this->getBinderMetadata($binder);
            } catch (ResolutionException $ex) {
                self::addFailedResolutionToMap($failedInterfacesToBinders, $ex->getInterface(), $binder);
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
     * @param array $failedInterfacesToBinders The map to add to
     * @param string $interface The interface that could not be resolved
     * @param Binder $binder The binder where the resolution failed
     */
    private static function addFailedResolutionToMap(
        array &$failedInterfacesToBinders,
        string $interface,
        Binder $binder
    ): void {
        if (!isset($failedInterfacesToBinders[$interface])) {
            $failedInterfaces[$interface] = [];
        }

        $failedInterfacesToBinders[$interface][] = $binder;
    }

    /**
     * Gets the metadata for a binder
     *
     * @param Binder $binder The binder whose metadata we want
     * @return BinderMetadata The binder's metadata
     * @throws ResolutionException Thrown if there was an error resolving any dependencies
     */
    private function getBinderMetadata(Binder $binder): BinderMetadata
    {
        $binderMetadataCollector = new BinderMetadataCollector($binder, $this->container);
        $binder->bind($binderMetadataCollector);

        return new BinderMetadata(
            $binder,
            $binderMetadataCollector->getBoundInterfaces(),
            $binderMetadataCollector->getResolvedInterfaces()
        );
    }

    /**
     * Retries any failed resolutions
     *
     * @param array $failedInterfacesToBinders The map of failed resolutions to retry
     * @return BinderMetadata[] The list of binder metadata that were retrieved from failed binders
     */
    private function retryFailedBinders(array &$failedInterfacesToBinders): array
    {
        $binderMetadatas = [];

        foreach ($failedInterfacesToBinders as $interface => $binders) {
            if (!$this->container->hasBinding($interface)) {
                // No point in retrying if the container still cannot resolve the interface
                continue;
            }

            foreach ($binders as $i => $binder) {
                try {
                    $binderMetadatas[] = $this->getBinderMetadata($binder);
                    // The binder must have been able to resolve everything, so remove it
                    unset($failedInterfacesToBinders[$interface][$i]);

                    // If this interface doesn't have any more failed binders, remove it
                    if (\count($failedInterfacesToBinders[$interface]) === 0) {
                        unset($failedInterfacesToBinders[$interface]);
                    }
                } catch (ResolutionException $ex) {
                    self::addFailedResolutionToMap(
                        $failedInterfacesToBinders,
                        $ex->getInterface(),
                        $binder
                    );
                }
            }
        }

        return $binderMetadatas;
    }
}
