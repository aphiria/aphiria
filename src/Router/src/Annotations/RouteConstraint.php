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
use InvalidArgumentException;

/**
 * Defines a route constraint attribute
 * @Annotation
 * @Target({"ANNOTATION"})
 */
final class RouteConstraint
{
    /** @var string The name of the class */
    public string $className;
    /** @var array The array of constructor params */
    public array $constructorParams = [];

    /**
     * @param array $values The mapping of value names to values
     */
    public function __construct(array $values)
    {
        if (isset($values['value'])) {
            $this->className = $values['value'];
            unset($values['value']);
        }

        if (isset($values['className'])) {
            $this->className = $values['className'];
        }

        if (empty($this->className)) {
            throw new InvalidArgumentException('Class name must be set');
        }

        $this->constructorParams = $values['constructorParams'] ?? [];
    }
}
