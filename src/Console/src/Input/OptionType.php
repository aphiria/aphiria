<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Input;

/**
 * Defines the different types of options
 */
enum OptionType: int
{
    /** The argument is required */
    case RequiredValue = 1;
    /** The argument is optional */
    case OptionalValue = 2;
    /** The argument is not allowed */
    case NoValue = 4;
    /** The argument is an array */
    case IsArray = 8;
}
