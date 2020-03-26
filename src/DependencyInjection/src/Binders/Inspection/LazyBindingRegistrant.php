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

use Aphiria\DependencyInjection\IContainer;

/**
 * Defines what registers our lazy bindings to the container
 */
final class LazyBindingRegistrant
{
    /** @var array The list of already-dispatched binder classes */
    private array $alreadyDispatchedBinderClasses = [];

    /**
     * Registers bindings found during inspection
     *
     * @param BinderBinding[] $binderBindings The bindings whose resolvers we're going to register
     * @param IContainer $container The container to use
     */
    public function registerBindings(array $binderBindings, IContainer $container): void
    {
        foreach ($binderBindings as $binderBinding) {
            $resolvingFactory = function () use ($binderBinding, $container) {
                /**
                 * To make sure this factory isn't used anymore to resolve the bound interface, unbind it and rely on the
                 * binding defined in the binder.  Otherwise, we'd get into an infinite loop every time we tried
                 * to resolve it.
                 */
                if ($binderBinding instanceof TargetedBinderBinding) {
                    $container->for(
                        $binderBinding->getTargetClass(),
                        fn (IContainer $container) => $container->unbind($binderBinding->getInterface())
                    );
                } else {
                    $container->unbind($binderBinding->getInterface());
                }

                $binder = $binderBinding->getBinder();
                $binderClass = \get_class($binder);

                // Make sure we don't double-dispatch this binder
                if (!isset($this->alreadyDispatchedBinderClasses[$binderClass])) {
                    $binder->bind($container);
                    $this->alreadyDispatchedBinderClasses[$binderClass] = true;
                }

                if ($binderBinding instanceof TargetedBinderBinding) {
                    return $container->for(
                        $binderBinding->getTargetClass(),
                        fn (IContainer $container) => $container->resolve($binderBinding->getInterface())
                    );
                }

                return $container->resolve($binderBinding->getInterface());
            };

            if ($binderBinding instanceof TargetedBinderBinding) {
                $container->for(
                    $binderBinding->getTargetClass(),
                    fn (IContainer $container) => $container->bindFactory($binderBinding->getInterface(), $resolvingFactory)
                );
            } else {
                $container->bindFactory($binderBinding->getInterface(), $resolvingFactory);
            }
        }
    }
}
