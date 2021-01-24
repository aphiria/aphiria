<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Binders\Metadata;

use Aphiria\DependencyInjection\Binders\Binder;

/**
 * Defines metadata about a binder
 */
final class BinderMetadata
{
    /**
     * @param Binder $binder The binder whose metadata this is
     * @param BoundInterface[] $boundInterfaces The list of bound interfaces in the binder
     * @param ResolvedInterface[] $resolvedInterfaces The list of resolved interfaces in the binder
     */
    public function __construct(private Binder $binder, private array $boundInterfaces, private array $resolvedInterfaces)
    {
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
