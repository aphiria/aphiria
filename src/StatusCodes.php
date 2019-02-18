<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console;

/**
 * Defines different console status codes
 */
final class StatusCodes
{
    /** Everything executed successfully */
    public const OK = 0;
    /** There was a warning */
    public const WARNING = 1;
    /** There was a non-fatal error */
    public const ERROR = 2;
    /** The application crashed */
    public const FATAL = 3;
}
