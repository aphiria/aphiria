<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Exceptions;

use Exception;

/**
 * Defines the interface for exception renderers to implement
 */
interface IExceptionRenderer
{
    /**
     * Renders an exception
     * Note: This should be done by writing to output directly, not returning an output value
     *
     * @param Exception $ex The exception that was thrown
     */
    public function render(Exception $ex): void;
}
