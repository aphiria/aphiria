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

use Aphiria\DependencyInjection\Binders\IBinderDispatcher;
use Aphiria\DependencyInjection\Binders\Inspection\Caching\IBinderBindingCache;
use Aphiria\DependencyInjection\IContainer;

/**
 * Defines a binder dispatcher that uses binding inspection
 */
final class BindingInspectorBinderDispatcher implements IBinderDispatcher
{
    /** @var IBinderBindingCache|null The cache to save binder bindings with, or null if not caching */
    private ?IBinderBindingCache $binderBindingCache;
    /** @var LazyBindingRegistrant The registrant for our lazy bindings */
    private LazyBindingRegistrant $lazyBindingRegistrant;
    /** @var BindingInspector The binding inspector to use */
    private BindingInspector $bindingInspector;

    /**
     * @param IBinderBindingCache|null $binderBindingCache The cache to use for binder bindings, or null if not caching
     * @param BindingInspector|null $bindingInspector The binding inspector to use, or null if using the default
     */
    public function __construct(
        IBinderBindingCache $binderBindingCache = null,
        BindingInspector $bindingInspector = null
    ) {
        $this->binderBindingCache = $binderBindingCache;
        $this->bindingInspector = $bindingInspector ?? new BindingInspector();
        $this->lazyBindingRegistrant = new LazyBindingRegistrant();
    }

    /**
     * @inheritdoc
     */
    public function dispatch(array $binders, IContainer $container): void
    {
        if ($this->binderBindingCache === null) {
            $binderBindings = $this->bindingInspector->getBindings($binders);
        } elseif (($binderBindings = $this->binderBindingCache->get()) === null) {
            $binderBindings = $this->bindingInspector->getBindings($binders);
            $this->binderBindingCache->set($binderBindings);
        }

        $this->lazyBindingRegistrant->registerBindings($binderBindings, $container);
    }
}
