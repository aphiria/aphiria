<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Annotations;

use Doctrine\Annotations\Annotation\Target;

/**
 * Defines a list of options for a group of routes
 * @Annotation
 * @Target({"CLASS","METHOD"})
 */
final class RouteGroup
{
    /** @var string The path of the route (defaults to an empty path) */
    public string $path = '';
    /** @var string|null The host of the route */
    public ?string $host = null;
    /** @var bool Whether or not this is HTTPS only */
    public bool $isHttpsOnly;
    /** @var array The custom attributes for the route */
    public array $attributes;
    /** @var RouteConstraint[] The list of route constraints */
    public array $constraints;

    /**
     * @param array $values The mapping of value names to values
     */
    public function __construct(array $values)
    {
        if (isset($values['value'])) {
            $this->path = $values['value'];
            unset($values['value']);
        }

        if (isset($values['path'])) {
            $this->path = $values['path'];
        }

        $this->host = $values['host'] ?? null;
        $this->isHttpsOnly = $values['isHttpsOnly'] ?? false;
        $this->attributes = $values['attributes'] ?? [];
        $this->constraints = $values['constraints'] ?? [];
    }
}
