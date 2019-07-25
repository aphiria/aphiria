<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/route-annotations/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\RouteAnnotations;

use Doctrine\Annotations\Annotation\Required;

/**
 * Defines the base class for routes
 * @Annotation
 */
abstract class Route
{
    /** @var array<string> The list of HTTP methods this route handles */
    public array $httpMethods;
    /** @var string The path of the route */
    public string $path;
    /** @var string|null The host of the route */
    public ?string $host = null;
    /** @var string|null The optional name of the route */
    public ?string $name = null;

    /**
     * @param array $values The list of values passed into the annotation
     */
    protected function __construct(array $values)
    {
        if (isset($values['value'])) {
            $this->path = $values['value'];
            unset($values['value']);
        }
    }
}
