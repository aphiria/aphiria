<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Reflection;

/**
 * Defines the interface for property type reflectors to implement
 */
interface IPropertyTypeReflector
{
    /**
     * Gets the list of types associated with a property
     *
     * @param string $class The name of the class that contains the property
     * @param string $property The name of the property whose type we want
     * @return array|null The list of types if the property had any, otherwise null
     */
    public function getType(string $class, string $property): ?array;
}
