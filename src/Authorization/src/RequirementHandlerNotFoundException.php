<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authorization;

use Exception;

/**
 * Defines the exception that is thrown when an authorization requirement handler is not found
 */
final class RequirementHandlerNotFoundException extends Exception
{
}
