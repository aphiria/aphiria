<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Requests;

/**
 * Defines the different types of arguments
 */
class ArgumentTypes
{
    /** The argument is required */
    public const REQUIRED = 1;
    /** The argument is optional */
    public const OPTIONAL = 2;
    /** The argument is an array */
    public const IS_ARRAY = 4;
}
