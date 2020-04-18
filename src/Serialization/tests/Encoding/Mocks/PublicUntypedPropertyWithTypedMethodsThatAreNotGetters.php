<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Serialization\Tests\Encoding\Mocks;

/**
 * Mocks a class with an untyped property and typed methods that are not getters
 */
final class PublicUntypedPropertyWithTypedMethodsThatAreNotGetters
{
    public $foo;

    public function doSomething(): self
    {
        return $this;
    }
}
