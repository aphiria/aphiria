<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Mocks;

/**
 * Defines a class with a mix of interfaces and primitives in its constructor
 */
class ConstructorWithMixOfInterfacesAndPrimitives
{
    /** @var IFoo A dependency */
    private IFoo $foo;
    /** @var int A primitive */
    private int $id;
    /** @var IPerson A dependency */
    private IPerson $person;

    /**
     * @param IFoo $foo A dependency
     * @param int $id A primitive
     * @param IPerson $person A dependency
     */
    public function __construct(IFoo $foo, $id, IPerson $person)
    {
        $this->foo = $foo;
        $this->id = $id;
        $this->person = $person;
    }

    /**
     * @return IFoo
     */
    public function getFoo(): IFoo
    {
        return $this->foo;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return IPerson
     */
    public function getPerson(): IPerson
    {
        return $this->person;
    }
}
