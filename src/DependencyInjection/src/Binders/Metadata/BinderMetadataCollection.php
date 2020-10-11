<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Binders\Metadata;

/**
 * Defines a collection of binder metadata
 */
final class BinderMetadataCollection
{
    /** @var BinderMetadata[][] The mapping of interfaces to binder metadata that universally resolve those interfaces */
    private array $universalResolutions = [];
    /** @var array The mapping of targets to interfaces to binders that resolve the interface for the target */
    private array $targetedResolutions = [];

    /**
     * @param BinderMetadata[] $binderMetadatas The list of all binder metadata
     */
    public function __construct(private array $binderMetadatas)
    {
        foreach ($this->binderMetadatas as $binderMetadata) {
            foreach ($binderMetadata->getResolvedInterfaces() as $resolvedInterface) {
                if ($resolvedInterface->getContext()->isTargeted()) {
                    $targetClass = $resolvedInterface->getContext()->getTargetClass();
                    $interface = $resolvedInterface->getInterface();

                    if (!isset($this->targetedResolutions[$targetClass])) {
                        $this->targetedResolutions[$targetClass] = [];
                    }

                    if (!isset($this->targetedResolutions[$targetClass][$interface])) {
                        $this->targetedResolutions[$targetClass][$interface] = [];
                    }

                    $this->targetedResolutions[$targetClass][$interface][] = $binderMetadata;
                } else {
                    $interface = $resolvedInterface->getInterface();

                    if (!isset($this->universalResolutions[$interface])) {
                        $this->universalResolutions[$interface] = [];
                    }

                    $this->universalResolutions[$interface][] = $binderMetadata;
                }
            }
        }
    }

    /**
     * Gets a list of all binder metadata in the collection
     *
     * @return BinderMetadata[] The list of all binder metadata
     */
    public function getAllBinderMetadata(): array
    {
        return $this->binderMetadatas;
    }

    /**
     * Gets a list of binder metadata that resolve an interface
     *
     * @param BoundInterface $boundInterface The bound interface to check for
     * @return BinderMetadata[] The list of binder metadata that resolve the input interface
     */
    public function getBinderMetadataThatResolveInterface(BoundInterface $boundInterface): array
    {
        /**
         * The following is a set of rules for determining what qualifies as resolving a bound interface
         *
         * Bound: Universal
         * Resolved: Universal
         * Return: true
         *
         * Bound: Universal
         * Resolved: Targeted
         * Return: true because resolving might fall back to universal
         *
         * Bound: Targeted
         * Resolved: Universal
         * Return: false
         *
         * Bound: Targeted
         * Resolved: Targeted
         * Return: true if same target
         */
        $binders = [];

        if ($boundInterface->getContext()->isTargeted()) {
            if (isset($this->targetedResolutions[$boundInterface->getContext()->getTargetClass()][$boundInterface->getInterface()])) {
                $binders = [
                    ...$binders,
                    ...$this->targetedResolutions[$boundInterface->getContext()->getTargetClass()][$boundInterface->getInterface()]
                ];
            }
        } else {
            if (isset($this->universalResolutions[$boundInterface->getInterface()])) {
                $binders = [
                    ...$binders,
                    ...$this->universalResolutions[$boundInterface->getInterface()]
                ];
            }

            foreach ($this->targetedResolutions as $targetClass => $interfacesToBinderMetadatas) {
                if (isset($interfacesToBinderMetadatas[$boundInterface->getInterface()])) {
                    $binders = [
                        ...$binders,
                        ...$interfacesToBinderMetadatas[$boundInterface->getInterface()]
                    ];
                }
            }
        }

        return $binders;
    }
}
