<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/net/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Requests;

use Opulence\Net\Http\HttpHeaderParser;
use Opulence\Net\Http\HttpHeaders;

/**
 * Defines the HTTP request header parser
 */
class RequestHeaderParser extends HttpHeaderParser
{
    /**
     * Gets whether or not the request headers have a JSON content type
     *
     * @param HttpHeaders $headers The headers to parse
     * @return bool True if the request has a JSON content type, otherwise false
     */
    public function isJson(HttpHeaders $headers) : bool
    {
        $contentType = null;
        $headers->tryGetFirst('Content-Type', $contentType);

        return preg_match("/application\/json/i", $contentType) === 1;
    }

    /**
     * Gets whether or not the request is a multipart request
     *
     * @param HttpHeaders $headers The headers to parse
     * @return bool True if the request is a multipart request, otherwise false
     */
    public function isMultipart(HttpHeaders $headers) : bool
    {
        $contentType = null;
        $headers->tryGetFirst('Content-Type', $contentType);

        return preg_match("/multipart\//i", $contentType) === 1;
    }

    /**
     * Gets whether or not the request headers have an XHR content type
     *
     * @param HttpHeaders $headers The headers to parse
     * @return bool True if the request has an XHR content type, otherwise false
     */
    public function isXhr(HttpHeaders $headers) : bool
    {
        $requestedWith = null;
        $headers->tryGetFirst('X-Requested-With', $requestedWith);

        return $requestedWith === 'XMLHttpRequest';
    }
}
