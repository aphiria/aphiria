<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\ContentNegotiation;

use InvalidArgumentException;
use Opulence\Net\Http\HttpException;
use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;

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
     * @param \object|string|int|float|array|null $rawBody The raw body to use in the response
     * @return IHttpResponseMessage The created response
     * @throws InvalidArgumentException Thrown if the body is not a supported type
     * @throws HttpException Thrown if the response content could not be negotiated
     */
    public function createResponse(
        IHttpRequestMessage $request,
        int $statusCode,
        ?HttpHeaders $headers,
        $rawBody
    ): IHttpResponseMessage;
}