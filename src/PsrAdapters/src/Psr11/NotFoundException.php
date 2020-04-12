<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\PsrAdapters\Psr11;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Defines the exception that's thrown when a container entry is not found
 */
final class NotFoundException extends Exception implements NotFoundExceptionInterface
{
    // Don't do anything
}
