<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Api\Exceptions;

use Aphiria\Exceptions\IExceptionRenderer;
use Aphiria\Net\Http\IResponse;
use Exception;

/**
 * Defines the exception renderer for API applications
 */
interface IApiExceptionRenderer extends IExceptionRenderer
{
    /**
     * Creates a response from an exception
     *
     * @param Exception $ex The exception that was thrown
     * @return IResponse The response
     */
    public function createResponse(Exception $ex): IResponse;
}
