<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/net/blob/master/LICENSE.md
 */

declare(strict_types=1);

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/aphiria/net/blob/master/LICENSE.md
 */

namespace Aphiria\Net\Http\ContentNegotiation;

use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\HttpHeaders;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IHttpResponseMessage;
use InvalidArgumentException;

/**
 * Defines interface for negotiated response factories to implement
 */
interface INegotiatedResponseFactory
{
    /**
     * Creates a response with a negotiated body
     *
     * @param IHttpRequestMessage $request The current request
     * @param int $statusCode The status code to use
     * @param HttpHeaders|null $headers The headers to use
     * @param object|string|int|float|array|null $rawBody The raw body to use in the response
     * @return IHttpResponseMessage The created response
     * @throws InvalidArgumentException Thrown if the body is not a supported type
     * @throws HttpException Thrown if the response content could not be negotiated
     */
    public function createResponse(
        IHttpRequestMessage $request,
        int $statusCode,
        HttpHeaders $headers = null,
        $rawBody = null
    ): IHttpResponseMessage;
}
