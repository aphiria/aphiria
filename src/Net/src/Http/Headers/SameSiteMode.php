<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http\Headers;

/**
 * Defines the list of cookie samesite modes
 */
enum SameSiteMode: string
{
    /** The lax same-site value */
    case Lax = 'lax';
    /** The strict same-site value */
    case Strict = 'strict';
    /** The none same-site value */
    case None = 'none';
}
