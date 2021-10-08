<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http;

/**
 * Defines the various request target types
 */
enum RequestTargetType
{
    /** @const The origin form */
    case OriginForm;
    /** @const The absolute form */
    case AbsoluteForm;
    /** @const The authority form */
    case AuthorityForm;
    /** @const The asterisk form */
    case AsteriskForm;
}
