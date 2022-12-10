<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Binders\Metadata;

use Aphiria\DependencyInjection\Context;

/**
 * Defines a resolved interface from a binder
 */
final readonly class ResolvedInterface
{
    /**
     * @param class-string $interface The interface that was resolved
     * @param Context $context The context that the resolution occurred in
     */
    public function __construct(public string $interface, public Context $context)
    {
    }
}
