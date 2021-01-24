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

use Aphiria\DependencyInjection\Context;

/**
 * Defines a resolved interface from a binder
 */
final class ResolvedInterface
{
    /**
     * @param class-string $interface The interface that was resolved
     * @param Context $context The context that the resolution occurred in
     */
    public function __construct(private string $interface, private Context $context)
    {
    }

    /**
     * Gets the interface that was resolved
     *
     * @return class-string The interface
     */
    public function getInterface(): string
    {
        return $this->interface;
    }

    /**
     * Gets the context that the resolution occurred in
     *
     * @return Context The context that the resolution occurred in
     */
    public function getContext(): Context
    {
        return $this->context;
    }
}
