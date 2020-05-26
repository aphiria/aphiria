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
 * Mocks a class with typed collections
 */
final class ClassWithTypedCollections
{
    /** @var ClassA<string, string> */
    private $property;

    /**
     * @param ClassA<string, string> $param
     */
    public function methodWithParam($param)
    {
    }

    /**
     * @return ClassA<string, string>
     */
    public function methodWithReturnType()
    {
    }
}
