<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Drivers;

use Exception;

/**
 * Defines the exception that's thrown when hidden input is not supported
 */
final class HiddenInputNotSupportedException extends Exception
{
    // Don't do anything
}
