<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Attributes;

use Attribute;

/**
 * Defines the base class for route attributes
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Route
{
    /**
     * @param string[] $httpMethods The list of HTTP methods this route handles
     * @param string $path The path of the route (defaults to an empty path)
     * @param string|null $host The host of the route, or null if matching any host
     * @param string|null $name The optional name of the route
     * @param bool $isHttpsOnly Whether or not this is HTTPS only
     * @param array<string, mixed> $parameters The custom parameters for the route
     */
    public function __construct(
        public array $httpMethods,
        public string $path = '',
        public ?string $host = null,
        public ?string $name = null,
        public bool $isHttpsOnly = false,
        public array $parameters = []
    ) {
    }
}
