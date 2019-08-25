<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Mocks;

use DateTime;

/**
 * Defines a class with a default value object
 */
final class ConstructorWithDefaultValueObject
{
    private DateTime $foo;

    public function __construct(DateTime $foo = null)
    {
        $this->foo = $foo ?? new DateTime();
    }

    public function getFoo(): DateTime
    {
        return $this->foo;
    }
}
