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
 * Defines a class that implements an interface for use in IoC tests
 */
class Foo implements IFoo
{
    /** @var IPerson A dependency */
    private IPerson $person;

    public function __construct(IPerson $person)
    {
        $this->person = $person;
    }

    /**
     * @inheritdoc
     */
    public function getClassName(): string
    {
        return __CLASS__;
    }
}
