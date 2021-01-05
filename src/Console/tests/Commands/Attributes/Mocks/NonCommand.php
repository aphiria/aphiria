<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Commands\Attributes\Mocks;

use Attribute;

/**
 * Defines an attribute that is not a command
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class NonCommand
{
}
