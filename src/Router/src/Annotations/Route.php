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

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Defines the base class for routes
 * @Annotation
 * @Target({"CLASS","METHOD"})
 */
class Route
{
    /** @var string[] The list of HTTP methods this route handles */
    public array $httpMethods;
    /** @var string The path of the route (defaults to an empty path) */
    public string $path = '';
    /** @var string|null The host of the route */
    public ?string $host = null;
    /** @var string|null The optional name of the route */
    public ?string $name = null;
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

        $this->httpMethods = $values['httpMethods'] ?? [];
        $this->host = $values['host'] ?? null;
        $this->name = $values['name'] ?? null;
        $this->isHttpsOnly = $values['isHttpsOnly'] ?? false;
        $this->attributes = $values['attributes'] ?? [];
        $this->constraints = $values['constraints'] ?? [];
    }
}
