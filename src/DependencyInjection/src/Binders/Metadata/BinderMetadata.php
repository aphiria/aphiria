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

/**
 * Defines metadata about a binder
 */
final class BinderMetadata
{
    /** @var Binder The binder whose metadata this is */
    private Binder $binder;
    /** @var BoundInterface[] The list of bound interfaces in the binder */
    private array $boundInterfaces;
    /** @var ResolvedInterface[] The list of resolved interfaces in the binder */
    private array $resolvedInterfaces;

    /**
     * @param Binder $binder The binder whose metadata this is
     * @param BoundInterface[] $boundInterfaces The list of bound interfaces in the binder
     * @param ResolvedInterface[] $resolvedInterfaces The list of resolved interfaces in the binder
     */
    public function __construct(Binder $binder, array $boundInterfaces, array $resolvedInterfaces)
    {
        $this->binder = $binder;
        $this->boundInterfaces = $boundInterfaces;
        $this->resolvedInterfaces = $resolvedInterfaces;
    }

    /**
     * Gets the binder whose metadata this is for
     *
     * @return Binder The binder
     */
    public function getBinder(): Binder
    {
        return $this->binder;
    }

    /**
     * Gets the list of bound interfaces
     *
     * @return BoundInterface[] The list of bound interfaces
     */
    public function getBoundInterfaces(): array
    {
        return $this->boundInterfaces;
    }

    /**
     * Gets the list of resolved interfaces
     *
     * @return ResolvedInterface[] The list of resolved interfaces
     */
    public function getResolvedInterfaces(): array
    {
        return $this->resolvedInterfaces;
    }
}
