<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Serialization\Tests\Encoding\Mocks;

/**
 * Mocks a derived class with properties
 */
class DerivedClassWithProperties extends BaseClassWithProperties
{
    private string $bar;

    public function __construct(string $foo, string $bar)
    {
        parent::__construct($foo);

        $this->bar = $bar;
    }
}
