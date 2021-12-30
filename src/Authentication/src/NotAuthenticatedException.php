<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authentication;

use Exception;

/**
 * Defines the exception that's thrown when a user's primary identity is not authenticated when attempting to log in
 */
final class NotAuthenticatedException extends Exception
{
}
