<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Mocks;

/**
 * Mocks a class that takes in an interface in its constructor
 */
class ConstructorWithInterface
{
    /** @var IFoo The object passed into the constructor */
    private IFoo $foo;

    /**
     * @param IFoo $foo The object to use
     */
    public function __construct(IFoo $foo)
    {
        $this->foo = $foo;
    }

    /**
     * @return IFoo
     */
    public function getFoo(): IFoo
    {
        return $this->foo;
    }
}
