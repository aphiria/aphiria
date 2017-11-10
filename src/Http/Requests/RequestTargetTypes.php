<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Requests;

/**
 * Defines the various request target types
 */
class RequestTargetTypes
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
