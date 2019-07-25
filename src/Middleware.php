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

use Doctrine\Annotations\Annotation\Target;

/**
 * Defines the middleware annotation
 * @Annotation
 * @Target({"METHOD","CLASS"})
 */
final class Middleware
{
    /** @var string The name of the middleware class */
    public string $className;
    /** @var array<string> The mapping of attribute names to values */
    public array $attributes = [];

    public function __construct(array $values)
    {
        if (isset($values['value'])) {
            $this->className = $values['value'];
            unset($values['value']);
        }
    }
}
