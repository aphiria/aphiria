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
final class RequestTargetTypes
{
    /** @const The origin form */
    public const ORIGIN_FORM = 'origin-form';
    /** @const The absolute form */
    public const ABSOLUTE_FORM = 'absolute-form';
    /** @const The authority form */
    public const AUTHORITY_FORM = 'authority-form';
    /** @const The asterisk form */
    public const ASTERISK_FORM = 'asterisk-form';
}
