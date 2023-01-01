<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection;

/**
 * Defines a targeted context
 */
final class TargetedContext extends Context
{
    /**
     * @param class-string|null $targetClass The targeted class, if there was one
     */
    public function __construct(?string $targetClass = null)
    {
        parent::__construct($targetClass, true, false);
    }
}
