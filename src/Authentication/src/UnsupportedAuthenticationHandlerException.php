<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authentication;

use Exception;

/**
 * Defines the exception that's thrown when an authentication handler does not support certain features
 */
final class UnsupportedAuthenticationHandlerException extends Exception
{
}
