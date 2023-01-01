<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http;

/**
 * Defines the various request target types
 */
enum RequestTargetType
{
    /** The origin form */
    case OriginForm;
    /** The absolute form */
    case AbsoluteForm;
    /** The authority form */
    case AuthorityForm;
    /** The asterisk form */
    case AsteriskForm;
}
