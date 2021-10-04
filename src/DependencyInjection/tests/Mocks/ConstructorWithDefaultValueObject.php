<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Mocks;

use DateTime;

/**
 * Defines a class with a default value object
 */
final class ConstructorWithDefaultValueObject
{
    public function __construct(public readonly DateTime $foo = new DateTime)
    {
    }
}
