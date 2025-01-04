<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Routing;

use RuntimeException;

/**
 * Defines an exception that's thrown when trying to create a route request
 */
final class RouteRequestCreationException extends RuntimeException {}
