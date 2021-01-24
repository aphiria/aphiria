<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection;

/**
 * Defines a universal context
 */
final class UniversalContext extends Context
{
    /**
     * @inheritdoc
     */
    public function getTargetClass(): ?string
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function isTargeted(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function isUniversal(): bool
    {
        return true;
    }
}
