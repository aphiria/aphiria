<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console;

/**
 * Defines different console status codes
 */
enum StatusCode: int
{
    /** Everything executed successfully */
    case Ok = 0;
    /** There was a warning */
    case Warning = 1;
    /** There was a non-fatal error */
    case Error = 2;
    /** The application crashed */
    case Fatal = 3;
}
