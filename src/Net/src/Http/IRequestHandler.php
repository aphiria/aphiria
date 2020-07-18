<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http;

use Exception;

/**
 * Defines the interface for request handlers to implement
 */
interface IRequestHandler
{
    /**
     * Handles a request and returns a response
     *
     * @param IRequest $request The incoming request
     * @return IResponse The response
     * @throws HttpException Thrown if there was an HTTP exception processing the request
     * @throws Exception Thrown if there was any other type of exception thrown while processing the request
     */
    public function handle(IRequest $request): IResponse;
}
