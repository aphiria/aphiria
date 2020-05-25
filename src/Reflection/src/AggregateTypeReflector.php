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

use InvalidArgumentException;

/**
 * Defines the type reflector that composes one or many other reflectors to try to get type information
 */
class AggregateTypeReflector implements ITypeReflector
{
    /** @var ITypeReflector[] The list of type reflectors to use */
    private array $typeReflectors;

    /**
     * @param ITypeReflector[] $typeReflectors The list of type reflectors to use
     * @throws InvalidArgumentException Thrown if the type reflectors were empty
     */
    public function __construct(array $typeReflectors)
    {
        if (\count($typeReflectors) === 0) {
            throw new InvalidArgumentException('List of type reflectors cannot be empty');
        }

        $this->typeReflectors = $typeReflectors;
    }

    /**
     * @inheritdoc
     */
    public function getParameterTypes(string $class, string $method, string $parameter): ?array
    {
        foreach ($this->typeReflectors as $typeReflector) {
            if (($types = $typeReflector->getParameterTypes($class, $method, $parameter)) !== null) {
                return $types;
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getPropertyTypes(string $class, string $property): ?array
    {
        foreach ($this->typeReflectors as $typeReflector) {
            if (($types = $typeReflector->getPropertyTypes($class, $property)) !== null) {
                return $types;
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getReturnTypes(string $class, string $method): ?array
    {
        foreach ($this->typeReflectors as $typeReflector) {
            if (($types = $typeReflector->getReturnTypes($class, $method)) !== null) {
                return $types;
            }
        }

        return null;
    }
}
