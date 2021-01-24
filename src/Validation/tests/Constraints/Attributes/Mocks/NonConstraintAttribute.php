<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints\Attributes\Mocks;

use Attribute;

/**
 * Mocks an attribute that is not a constraint attribute
 */
#[Attribute(Attribute::TARGET_ALL)]
final class NonConstraintAttribute
{
}
