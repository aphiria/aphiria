<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Input;

/**
 * Defines the different types of options
 */
enum OptionType: int
{
    /** The option is an array */
    case IsArray = 8;
    /** The option is not allowed */
    case NoValue = 4;
    /** The option is optional */
    case OptionalValue = 2;
    /** The option is required */
    case RequiredValue = 1;
}
