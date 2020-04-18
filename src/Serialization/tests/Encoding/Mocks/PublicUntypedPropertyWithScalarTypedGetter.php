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
 * Mocks a class with a public untyped property and a typed getter
 */
final class PublicUntypedPropertyWithScalarTypedGetter
{
    public $foo;

    public function getFoo(): string
    {
        return $this->foo;
    }
}
