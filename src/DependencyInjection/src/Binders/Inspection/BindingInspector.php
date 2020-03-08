<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Binders\Inspection;

use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\ResolutionException;

/**
 * Defines the inspector that can determine what bindings are registered in a binder
 */
final class BindingInspector
{
    /** @var BindingInspectionContainer The container that can determine bindings */
    private BindingInspectionContainer $container;

    /**
     * @param BindingInspectionContainer|null $inspectionContainer The container that can determine bindings
     */
    public function __construct(BindingInspectionContainer $inspectionContainer = null)
    {
        $this->container = $inspectionContainer ?? new BindingInspectionContainer();
    }

    /**
     * Finds the bindings that were registered in a list of binders
     *
     * @param Binder[] $binders The binders to inspect
     * @return BinderBinding[] The list of bindings that were found
     * @throws ImpossibleBindingException Thrown if the bindings are not possible to resolve
     */
    public function getBindings(array $binders): array
    {
        $failedInterfacesToBinders = [];

        foreach ($binders as $binder) {
            try {
                $this->inspectBinder($binder);
            } catch (ResolutionException $ex) {
                self::addFailedResolutionToMap($failedInterfacesToBinders, $ex->getInterface(), $binder);
            }

            $this->retryFailedBinders($failedInterfacesToBinders);
        }

        if (\count($failedInterfacesToBinders) > 0) {
            throw new ImpossibleBindingException($failedInterfacesToBinders);
        }

        return $this->container->getBindings();
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
     * Inspects an individual binder for bindings
     *
     * @param Binder $binder The binder to inspect
     * @throws ResolutionException Thrown if there was an error resolving any dependencies
     */
    private function inspectBinder(Binder $binder): void
    {
        $this->container->setBinder($binder);
        $binder->bind($this->container);
    }

    /**
     * Retries any failed resolutions
     *
     * @param array $failedInterfacesToBinders The map of failed resolutions to retry
     */
    private function retryFailedBinders(array &$failedInterfacesToBinders): void
    {
        foreach ($failedInterfacesToBinders as $interface => $binders) {
            if (!$this->container->hasBinding($interface)) {
                // No point in retrying if the container still cannot resolve the interface
                continue;
            }

            foreach ($binders as $i => $binder) {
                try {
                    $this->inspectBinder($binder);
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
    }
}
