<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Reflection\Tests\Mocks;

use Aphiria\Reflection\Tests\Mocks\Finder\ClassA;

/**
 * Mocks a class with typed objects
 */
final class ClassWithTypedObjects
{
    /** @var ClassA */
    private $property;

    /**
     * @param ClassA $param
     */
    public function methodWithParam($param): void
    {
    }

    /**
     * @return ClassA
     */
    public function methodWithReturnType()
    {
    }
}
