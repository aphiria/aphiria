<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Attributes;

use Attribute;

/**
 * Defines the attribute that indicates that a class is a controller
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class Controller
{
    /**
     * @param string $path The path prefix to apply to all the routes in the controller (defaults to an empty path)
     * @param string|null $host The host to apply to all the routes in the controller
     * @param bool $isHttpsOnly Whether or not all the routes in the controller are HTTPS only
     * @param array<string, mixed> $parameters The mapping of custom parameter names to values for all the routes in the controller
     */
    public function __construct(
        public string $path = '',
        public ?string $host = null,
        public bool $isHttpsOnly = false,
        public array $parameters = []
    ) {
    }
}
