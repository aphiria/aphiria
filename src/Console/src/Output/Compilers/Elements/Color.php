<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output\Compilers\Elements;

/**
 * Defines the list of colors used by a console
 */
enum Color: string
{
    /** The color black */
    case Black = 'black';
    /** The color blue */
    case Blue = 'blue';
    /** The color cyan */
    case Cyan = 'cyan';
    /** The color green */
    case Green = 'green';
    /** The color magenta */
    case Magenta = 'magenta';
    /** The color red */
    case Red = 'red';
    /** The color white */
    case White = 'white';
    /** The color yellow */
    case Yellow = 'yellow';
}
