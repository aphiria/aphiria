<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Discoverers;

final class DiscovererBuilder implements IComponentBuilder
{
    public function __construct(private readonly IDiscovererResolver $discovererResolver) {}

    /**
     * @inheritdoc
     */
    public function build(array $components): void
    {
        // TODO: Missing logic to actually build the discoverers
    }
}
