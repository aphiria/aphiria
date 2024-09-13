<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Api\Exceptions;

use Aphiria\Exceptions\IExceptionRenderer;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Http\IResponseFactory;
use Exception;

/**
 * Defines the exception renderer for API applications
 */
interface IApiExceptionRenderer extends IExceptionRenderer
{
    /** @var IRequest The current request */
    public IRequest $request { set; }
    /** @var IResponseFactory The response factory to render exceptions with */
    public IResponseFactory $responseFactory { set; }

    /**
     * Creates a response from an exception
     *
     * @param Exception $ex The exception that was thrown
     * @return IResponse The response
     */
    public function createResponse(Exception $ex): IResponse;
}
