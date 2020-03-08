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

/**
 * Defines the binder component
 */
class BinderComponent implements IComponent
{
    /** @var IBinderDispatcher The binder dispatcher */
    private IBinderDispatcher $binderDispatcher;
    /** @var Binder[] The list of binders to dispatch */
    private array $binders = [];

    /**
     * @param IBinderDispatcher $binderDispatcher The binder dispatcher
     */
    public function __construct(IBinderDispatcher $binderDispatcher)
    {
        $this->binderDispatcher = $binderDispatcher;
    }

    /**
     * @inheritdoc
     */
    public function initialize(): void
    {
        $this->binderDispatcher->dispatch($this->binders);
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
