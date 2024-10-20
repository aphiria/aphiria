<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http;

use InvalidArgumentException;

/**
 * Defines interface for response factories to implement
 */
interface IResponseFactory
{
    /**
     * Creates a response from the input parameters
     *
     * @param IRequest $request The current request
     * @param HttpStatusCode|int $statusCode The status code to use
     * @param Headers|null $headers The headers to use
     * @param object|string|int|float|array|null $rawBody The raw body to use in the response
     * @return IResponse The created response
     * @throws InvalidArgumentException Thrown if the body is not a supported type
     * @throws HttpException Thrown if there was an error reading request data
     */
    public function createResponse(
        IRequest $request,
        HttpStatusCode|int $statusCode,
        ?Headers $headers = null,
        object|string|int|float|array|null $rawBody = null
    ): IResponse;
}
