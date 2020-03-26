<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\DependencyInjection\Components;

use Aphiria\Application\IComponent;
use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\Binders\IBinderDispatcher;
use Aphiria\DependencyInjection\IContainer;

/**
 * Defines the binder component
 */
class BinderComponent implements IComponent
{
    /** @var IBinderDispatcher The binder dispatcher */
    private IBinderDispatcher $binderDispatcher;
    /** @var IContainer The container to dispatch binders with */
    private IContainer $container;
    /** @var Binder[] The list of binders to dispatch */
    private array $binders = [];

    /**
     * @param IBinderDispatcher $binderDispatcher The binder dispatcher
     * @param IContainer $container The container to dispatch binders with
     */
    public function __construct(IBinderDispatcher $binderDispatcher, IContainer $container)
    {
        $this->binderDispatcher = $binderDispatcher;
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public function build(): void
    {
        $this->binderDispatcher->dispatch($this->binders, $this->container);
    }

    /**
     * Adds binders to dispatch
     *
     * @param Binder|Binder[] $binders The binders to add
     * @return self For chaining
     */
    public function withBinders($binders): self
    {
        if ($binders instanceof Binder) {
            $this->binders[] = $binders;
        } elseif (\is_array($binders)) {
            $this->binders = [...$this->binders, ...$binders];
        }

        return $this;
    }
}
