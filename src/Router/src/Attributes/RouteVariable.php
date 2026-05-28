<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2026 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Attributes;

use Attribute;

/**
 * Defines the attribute for describing route parameters that should be resolved from the route variables
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class RouteVariable
{
    /**
     * @param string|null $name The optional name of the route variable to resolve the value from
     */
    public function __construct(public readonly ?string $name = null) {}
}
