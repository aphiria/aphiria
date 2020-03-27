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

/**
 * Defines a resolved interface from a binder
 */
final class ResolvedInterface
{
    /** @var string The interface that was resolved */
    private string $interface;
    /** @var string|null The optional target class for the resolved interface */
    private ?string $targetClass;

    /**
     * @param string $interface The interface that was resolved
     * @param string|null $targetClass The optional target class for the resolved interface
     */
    public function __construct(string $interface, string $targetClass = null)
    {
        $this->interface = $interface;
        $this->targetClass = $targetClass;
    }

    /**
     * Gets the interface that was resolved
     *
     * @return string The interface
     */
    public function getInterface(): string
    {
        return $this->interface;
    }

    /**
     * Gets the target for the resolved interface
     *
     * @return string|null The target class if there was one, otherwise null
     */
    public function getTargetClass(): ?string
    {
        return $this->targetClass;
    }

    /**
     * Gets whether or not the resolved interface is targeted
     *
     * @return bool True if the resolved interface is targeted, otherwise false
     */
    public function isTargeted(): bool
    {
        return $this->targetClass !== null;
    }
}
