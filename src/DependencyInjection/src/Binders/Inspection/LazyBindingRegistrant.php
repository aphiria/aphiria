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
    /** @var IContainer The container to bind our resolvers to */
    private IContainer $container;
    /** @var array The list of already-dispatched binder classes */
    private array $alreadyDispatchedBinderClasses = [];

    /**
     * @param IContainer $container The container to bind our resolvers to
     */
    public function __construct(IContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Registers bindings found during inspection
     *
     * @param BinderBinding[] $binderBindings The bindings whose resolvers we're going to register
     */
    public function registerBindings(array $binderBindings): void
    {
        foreach ($binderBindings as $binderBinding) {
            $resolvingFactory = function () use ($binderBinding) {
                /**
                 * To make sure this factory isn't used anymore to resolve the bound interface, unbind it and rely on the
                 * binding defined in the binder.  Otherwise, we'd get into an infinite loop every time we tried
                 * to resolve it.
                 */
                if ($binderBinding instanceof TargetedBinderBinding) {
                    $this->container->for(
                        $binderBinding->getTargetClass(),
                        fn (IContainer $container) => $container->unbind($binderBinding->getInterface())
                    );
                } else {
                    $this->container->unbind($binderBinding->getInterface());
                }

                $binder = $binderBinding->getBinder();
                $binderClass = \get_class($binder);

                // Make sure we don't double-dispatch this binder
                if (!isset($this->alreadyDispatchedBinderClasses[$binderClass])) {
                    $binder->bind($this->container);
                    $this->alreadyDispatchedBinderClasses[$binderClass] = true;
                }

                if ($binderBinding instanceof TargetedBinderBinding) {
                    return $this->container->for(
                        $binderBinding->getTargetClass(),
                        fn (IContainer $container) => $container->resolve($binderBinding->getInterface())
                    );
                }

                return $this->container->resolve($binderBinding->getInterface());
            };

            if ($binderBinding instanceof TargetedBinderBinding) {
                $this->container->for(
                    $binderBinding->getTargetClass(),
                    fn (IContainer $container) => $container->bindFactory($binderBinding->getInterface(), $resolvingFactory)
                );
            } else {
                $this->container->bindFactory($binderBinding->getInterface(), $resolvingFactory);
            }
        }
    }
}
