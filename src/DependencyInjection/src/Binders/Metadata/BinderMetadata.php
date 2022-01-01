<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
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
     * @param list<BoundInterface> $boundInterfaces The list of bound interfaces in the binder
     * @param list<ResolvedInterface> $resolvedInterfaces The list of resolved interfaces in the binder
     */
    public function __construct(
        public readonly Binder $binder,
        public readonly array $boundInterfaces,
        public readonly array $resolvedInterfaces
    ) {
    }
}
