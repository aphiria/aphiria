<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http;

/**
 * Defines the interface for HTTP clients to implement
 */
interface IHttpClient
{
    /**
     * Sends a request through the application and gets a response
     *
     * @param IRequest $request The request to send
     * @return IResponse The returned response
     * @throws HttpException Thrown if there was an error handling the request
     */
    public function send(IRequest $request): IResponse;
}
