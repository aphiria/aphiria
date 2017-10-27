<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Responses;

use InvalidArgumentException;
use Opulence\Net\Http\StringBody;

/**
 * Defines the HTTP response message formatter
 */
class ResponseFormatter
{
    /**
     * Sets up the response to redirect to a particular URI
     *
     * @param IHttpResponseMessage $response The response to format
     * @param string $uri The URI to redirect to
     * @param int $statusCode The status code
     */
    public function redirectToUri(IHttpResponseMessage $response, string $uri, int $statusCode = 302) : void
    {
        $response->setStatusCode($statusCode);
        $response->getHeaders()->add('Location', $uri);
    }

    /**
     * Writes JSON to the response
     *
     * @param IHttpResponseMessage $response The response to write to
     * @param array $content The JSON to write
     * @throws InvalidArgumentException Thrown if the input JSON is incorrectly formatted
     */
    public function writeJson(IHttpResponseMessage $response, array $content) : void
    {
        $json = json_encode($content);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Failed to JSON encode content: ' . json_last_error_msg());
        }

        $response->getHeaders()->add('Content-Type', 'application/json');
        $response->setBody(new StringBody($json));
    }
}
