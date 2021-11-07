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
 * Defines an interface that was bound in a binder
 */
final class BoundInterface
{
    /**
     * @param class-string $interface The interface that was bound
     * @param Context $context The context that the binding occurred in
     */
    public function __construct(public readonly string $interface, public readonly Context $context)
    {
    }
}
