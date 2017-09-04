<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/net/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Responses;

use Opulence\IO\Streams\IStream;

/**
 * Defines the interface for stream HTTP response factories to implement
 */
interface IStreamHttpResponseFactory
{
    /**
     * Creates a stream response
     *
     * @param IStream $stream The stream
     * @param int $statusCode The HTTP status code
     * @param array $headers The response headers
     * @return IHttpResponseMessage The created response message
     */
    public function createResponse(
        IStream $stream,
        int $statusCode = HttpStatusCodes::HTTP_OK,
        array $headers = []
    ) : IHttpResponseMessage;
}
