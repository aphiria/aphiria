<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Attributes;

use Attribute;

/**
 * Defines the attribute that indicates that a class is a controller
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class Controller
{
    // Don't do anything
}
