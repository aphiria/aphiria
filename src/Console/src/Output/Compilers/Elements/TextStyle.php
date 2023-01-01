<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output\Compilers\Elements;

/**
 * Defines the list of text styles
 */
enum TextStyle: string
{
    /** Text is blinking */
    case Blink = 'blink';
    /** Text is bold */
    case Bold = 'bold';
    /** Text is underlined */
    case Underline = 'underline';
}
