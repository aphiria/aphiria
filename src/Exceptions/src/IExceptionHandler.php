<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Exceptions;

use Exception;

/**
 * Defines the interface for exception handlers to implement
 */
interface IExceptionHandler
{
    /**
     * Handles an exception
     *
     * @param Exception $ex The exception that was thrown
     */
    public function handle(Exception $ex): void;
}
