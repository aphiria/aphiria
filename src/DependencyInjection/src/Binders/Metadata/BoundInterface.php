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

use Aphiria\DependencyInjection\Context;

/**
 * Defines an interface that was bound in a binder
 */
final class BoundInterface
{
    /** @var string The interface that was bound */
    private string $interface;
    /** @var Context The context that the binding occurred in */
    private Context $context;

    /**
     * @param string $interface The interface that was bound
     * @param Context $context The context that the binding occurred in
     */
    public function __construct(string $interface, Context $context)
    {
        $this->interface = $interface;
        $this->context = $context;
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
     * Gets the context that the binding occurred in
     *
     * @return Context The context that the binding occurred in
     */
    public function getContext(): Context
    {
        return $this->context;
    }
}
