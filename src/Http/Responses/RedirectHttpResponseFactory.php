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
 * Defines the redirect HTTP response factory
 */
class RedirectHttpResponseFactory
{
    /**
     * Creates a redirect response
     *
     * @param string $uri The URI to redirect to
     * @param int $statusCode The HTTP status code
     * @param array $headers The response headers
     * @return IHttpResponseMessage The creates response message
     */
    public function createResponse(
        string $uri,
        int $statusCode = HttpStatusCodes::HTTP_FOUND,
        array $headers = []
    ) : IHttpResponseMessage {
        // Todo
    }
}
