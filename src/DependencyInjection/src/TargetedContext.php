<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection;

/**
 * Defines a targeted context
 */
final class TargetedContext extends Context
{
    /**
     * @param string|null $targetClass The targeted class, if there was one
     */
    public function __construct(private ?string $targetClass = null)
    {
    }

    /**
     * @inheritdoc
     */
    public function getTargetClass(): ?string
    {
        return $this->targetClass;
    }

    /**
     * @inheritdoc
     */
    public function isTargeted(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isUniversal(): bool
    {
        return false;
    }
}
