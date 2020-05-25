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
 * Defines the interface for type reflectors to implement
 */
interface ITypeReflector
{
    /**
     * Gets the list of types associated with a parameter
     *
     * @param string $class The name of the class that contains the method parameter
     * @param string $method The name of the method whose parameter type we want
     * @param string $parameter The name of the parameter whose type we want
     * @return Type[]|null The list of types if the parameter had any, otherwise null
     */
    public function getParameterTypes(string $class, string $method, string $parameter): ?array;

    /**
     * Gets the list of types associated with a property
     *
     * @param string $class The name of the class that contains the property
     * @param string $property The name of the property whose type we want
     * @return Type[]|null The list of types if the property had any, otherwise null
     */
    public function getPropertyTypes(string $class, string $property): ?array;

    /**
     * Gets the list of types that a method returns
     *
     * @param string $class The name of the class that contains the method
     * @param string $method The name of the method whose return type we want
     * @return array|null The list of types if the method had any, otherwise null
     */
    public function getReturnTypes(string $class, string $method): ?array;
}
