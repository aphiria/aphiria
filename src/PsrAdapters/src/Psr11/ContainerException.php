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
use Psr\Container\ContainerExceptionInterface;

/**
 * Defines the exception that's thrown when the container could not resolve an interface
 */
final class ContainerException extends Exception implements ContainerExceptionInterface
{
    // Don't do anything
}
