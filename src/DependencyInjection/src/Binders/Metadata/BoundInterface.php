<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Binders\Metadata;

use Aphiria\DependencyInjection\Context;

/**
 * Defines an interface that was bound in a binder
 */
final class BoundInterface
{
    /**
     * @param class-string $interface The interface that was bound
     * @param Context $context The context that the binding occurred in
     */
    public function __construct(private string $interface, private Context $context)
    {
    }

    /**
     * Gets the interface that was bound
     *
     * @return class-string The interface
     */
    public function getInterface(): string
    {
        return $this->interface;
    }

    /**
     * Gets the context that the binding occurred in
     *
     * @return Context The context that the binding occurred in
     */
    public function getContext(): Context
    {
        return $this->context;
    }
}
