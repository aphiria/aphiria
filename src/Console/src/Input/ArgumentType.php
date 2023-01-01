<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Input;

/**
 * Defines the different types of arguments
 */
enum ArgumentType: int
{
    /** The argument is required */
    case Required = 1;
    /** The argument is optional */
    case Optional = 2;
    /** The argument is an array */
    case IsArray = 4;
}
