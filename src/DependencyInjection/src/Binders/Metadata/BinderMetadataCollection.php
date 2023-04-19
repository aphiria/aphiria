<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Binders\Metadata;

/**
 * Defines a collection of binder metadata
 */
final class BinderMetadataCollection
{
    /** @var array<class-string, array<class-string, list<BinderMetadata>>> The mapping of targets to interfaces to binder metadata that resolve the interface for the target */
    private array $targetedResolutions = [];
    /** @var array<class-string, list<BinderMetadata>> The mapping of interfaces to binder metadata that universally resolve those interfaces */
    private array $universalResolutions = [];

    /**
     * @param list<BinderMetadata> $binderMetadatas The list of all binder metadata
     */
    public function __construct(public readonly array $binderMetadatas)
    {
        foreach ($this->binderMetadatas as $binderMetadata) {
            foreach ($binderMetadata->resolvedInterfaces as $resolvedInterface) {
                if ($resolvedInterface->context->isTargeted) {
                    /** @var class-string $targetClass We know that this will be set because it's targeted */
                    $targetClass = $resolvedInterface->context->targetClass;
                    $interface = $resolvedInterface->interface;

                    if (!isset($this->targetedResolutions[$targetClass])) {
                        $this->targetedResolutions[$targetClass] = [];
                    }

                    if (!isset($this->targetedResolutions[$targetClass][$interface])) {
                        $this->targetedResolutions[$targetClass][$interface] = [];
                    }

                    $this->targetedResolutions[$targetClass][$interface][] = $binderMetadata;
                } else {
                    $interface = $resolvedInterface->interface;

                    if (!isset($this->universalResolutions[$interface])) {
                        $this->universalResolutions[$interface] = [];
                    }

                    $this->universalResolutions[$interface][] = $binderMetadata;
                }
            }
        }
    }

    /**
     * Gets a list of binder metadata that resolve an interface
     *
     * @param BoundInterface $boundInterface The bound interface to check for
     * @return list<BinderMetadata> The list of binder metadata that resolve the input interface
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
         *
         * @var list<BinderMetadata> $binders
         */
        $binders = [];

        if ($boundInterface->context->isTargeted) {
            if (isset($this->targetedResolutions[$boundInterface->context->targetClass][$boundInterface->interface])) {
                $binders = [
                    ...$binders,
                    ...$this->targetedResolutions[$boundInterface->context->targetClass][$boundInterface->interface]
                ];
            }
        } else {
            if (isset($this->universalResolutions[$boundInterface->interface])) {
                $binders = [
                    ...$binders,
                    ...$this->universalResolutions[$boundInterface->interface]
                ];
            }

            foreach ($this->targetedResolutions as $interfacesToBinderMetadatas) {
                if (isset($interfacesToBinderMetadatas[$boundInterface->interface])) {
                    $binders = [
                        ...$binders,
                        ...$interfacesToBinderMetadatas[$boundInterface->interface]
                    ];
                }
            }
        }

        return $binders;
    }
}
