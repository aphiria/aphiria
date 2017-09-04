<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/net/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Responses;

/**
 * Defines the interface for JSON HTTP response factories to implement
 */
interface IJsonHttpResponseFactory
{
    /**
     * Creates a JSON response
     *
     * @param array|string $content The response content
     * @param int $statusCode The HTTP status code
     * @param array $headers The response headers
     * @return IHttpResponseMessage The created response message
     */
    public function createResponse(
        $content,
        int $statusCode = HttpStatusCodes::HTTP_OK,
        array $headers = []
    ) : IHttpResponseMessage;
}
