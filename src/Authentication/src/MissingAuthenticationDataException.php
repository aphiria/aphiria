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
 * Defines the exception that's thrown when data necessary to authenticate a request is missing
 */
final class MissingAuthenticationDataException extends Exception
{
}
