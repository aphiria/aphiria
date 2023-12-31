<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Mocks;

/**
 * Mocks a class with an interface in its constructor and setters for use in IoC tests
 */
class ConstructorWithInterfaceAndSetters
{
    /** @var IFoo A dependency */
    private IFoo $constructorDependency;
    /** @var IPerson A dependency */
    private IPerson $setterDependency;

    public function __construct(IFoo $foo)
    {
        $this->constructorDependency = $foo;
    }

    /**
     * @return IFoo
     */
    public function getConstructorDependency(): IFoo
    {
        return $this->constructorDependency;
    }

    /**
     * @return IPerson
     */
    public function getSetterDependency(): IPerson
    {
        return $this->setterDependency;
    }

    /**
     * @param IPerson $setterDependency
     */
    public function setSetterDependency(IPerson $setterDependency): void
    {
        $this->setterDependency = $setterDependency;
    }
}
