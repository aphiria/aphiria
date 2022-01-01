<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection;

/**
 * Defines the context that resolutions and binders occur in
 */
abstract class Context
{
    /**
     * @param class-string|null $targetClass The targeted class, if there was one
     * @param bool $isTargeted Whether or not the context is targeted
     * @param bool $isUniversal Whether or not the context is universal
     */
    protected function __construct(
        public readonly ?string $targetClass,
        public readonly bool $isTargeted,
        public readonly bool $isUniversal
    ) {
    }
}
