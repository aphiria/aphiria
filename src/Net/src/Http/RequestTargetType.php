<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http;

/**
 * Defines the various request target types
 */
enum RequestTargetType
{
    /** The absolute form */
    case AbsoluteForm;
    /** The asterisk form */
    case AsteriskForm;
    /** The authority form */
    case AuthorityForm;
    /** The origin form */
    case OriginForm;
}
