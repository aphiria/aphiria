<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation;

use Exception;

/**
 * Defines an exception that's thrown when a circular dependency is detected
 */
final class CircularDependencyException extends Exception
{
    // Don't do anything
}
