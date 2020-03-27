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
 * Defines an interface that was bound in a binder
 */
final class BoundInterface
{
    /** @var string The interface that was bound */
    private string $interface;
    /** @var string|null The optional target class for the bound interface */
    private ?string $targetClass;

    /**
     * @param string $interface The interface that was bound
     * @param string|null $targetClass The optional target class for the bound interface
     */
    public function __construct(string $interface, string $targetClass = null)
    {
        $this->interface = $interface;
        $this->targetClass = $targetClass;
    }

    /**
     * Gets the interface that was bound
     *
     * @return string The interface
     */
    public function getInterface(): string
    {
        return $this->interface;
    }

    /**
     * Gets the target for the bound interface
     *
     * @return string|null The target class if there was one, otherwise null
     */
    public function getTargetClass(): ?string
    {
        return $this->targetClass;
    }

    /**
     * Gets whether or not the bound interface is targeted
     *
     * @return bool True if the bound interface is targeted, otherwise false
     */
    public function isTargeted(): bool
    {
        return $this->targetClass !== null;
    }
}
