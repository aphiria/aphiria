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

use DateTime;

/**
 * Mocks a class with a nullable object parameter
 */
final class ConstructorWithNullableObject
{
    private DateTime $foo;

    public function __construct(?DateTime $foo)
    {
        $this->foo = $foo ?? new DateTime();
    }

    public function getFoo(): DateTime
    {
        return $this->foo;
    }
}
