<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection;

use Exception;

/**
 * Defines the exception that's thrown when a method or closure could not be called by the container
 */
final class CallException extends Exception
{
    // Don't do anything
}
