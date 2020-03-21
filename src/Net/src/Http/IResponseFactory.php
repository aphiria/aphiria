<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

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
     * @param IHttpRequestMessage $request The current request
     * @param int $statusCode The status code to use
     * @param HttpHeaders|null $headers The headers to use
     * @param object|string|int|float|array|null $rawBody The raw body to use in the response
     * @return IHttpResponseMessage The created response
     * @throws InvalidArgumentException Thrown if the body is not a supported type
     * @throws HttpException Thrown if there was an error reading request data
     */
    public function createResponse(
        IHttpRequestMessage $request,
        int $statusCode,
        HttpHeaders $headers = null,
        $rawBody = null
    ): IHttpResponseMessage;
}
