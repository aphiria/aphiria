<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Attributes;

use Attribute;

/**
 * Defines the attribute for a list of options for a group of routes
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class RouteGroup
{
    /**
     * @param string $path The path prefix to apply to all the routes (defaults to an empty path)
     * @param string|null $host The host to apply to all the routes
     * @param bool $isHttpsOnly Whether or not all the routes are HTTPS only
     * @param array $attributes The mapping of custom attribute names to values for all the routes
     */
    public function __construct(
        public string $path = '',
        public ?string $host = null,
        public bool $isHttpsOnly = false,
        public array $attributes = []
    ) {
    }
}
