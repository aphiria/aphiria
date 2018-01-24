<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting;

use Opulence\Collections\IImmutableDictionary;
use Opulence\Net\Http\Headers\AcceptCharsetHeaderValue;
use Opulence\Net\Http\Headers\AcceptMediaTypeHeaderValue;
use Opulence\Net\Http\Headers\ContentTypeHeaderValue;
use Opulence\Net\Http\HttpHeaders;

/**
 * Defines the request header parser
 */
class RequestHeaderParser extends HttpHeaderParser
{
    /**
     * Parses the Accept-Charset header
     *
     * @param HttpHeaders $headers The request headers to parse
     * @return AcceptCharsetHeaderValue[] The list of charset header values
     */
    public function parseAcceptCharsetHeader(HttpHeaders $headers) : array
    {
        $headerValues = [];

        if (!$headers->tryGet('Accept-Charset', $headerValues)) {
            return [];
        }

        $parsedHeaderValues = [];

        for ($i = 0;$i < count($headerValues);$i++) {
            $parsedHeaderParameters = $this->parseParameters($headers, 'Accept-Charset', $i);
            // The first value should always be the charset
            $charset = $parsedHeaderParameters->getKeys()[0];
            $parsedHeaderValues[] = new AcceptCharsetHeaderValue($charset, $parsedHeaderParameters);
        }

        return $parsedHeaderValues;
    }

    /**
     * Parses the Accept header
     *
     * @param HttpHeaders $headers The request headers to parse
     * @return AcceptMediaTypeHeaderValue[] The list of media type header values
     */
    public function parseAcceptHeader(HttpHeaders $headers) : array
    {
        $headerValues = [];

        if (!$headers->tryGet('Accept', $headerValues)) {
            return [];
        }

        $parsedHeaderValues = [];

        for ($i = 0;$i < count($headerValues);$i++) {
            $parsedHeaderParameters = $this->parseParameters($headers, 'Accept', $i);
            // The first value should always be the media type
            $mediaType = $parsedHeaderParameters->getKeys()[0];
            $parsedHeaderValues[] = new AcceptMediaTypeHeaderValue($mediaType, $parsedHeaderParameters);
        }

        return $parsedHeaderValues;
    }

    /**
     * Parses the Content-Type header
     *
     * @param HttpHeaders $headers The request headers to parse
     * @return ContentTypeHeaderValue|null The parsed header if one exists, otherwise null
     */
    public function parseContentTypeHeader(HttpHeaders $headers) : ?ContentTypeHeaderValue
    {
        if (!$headers->containsKey('Content-Type')) {
            return null;
        }

        $contentTypeHeaderParameters = $this->parseParameters($headers, 'Content-Type', 0);
        $contentType = $contentTypeHeaderParameters->getKeys()[0];

        return new ContentTypeHeaderValue($contentType, $contentTypeHeaderParameters);
    }

    /**
     * Parses the request headers for all cookie values
     *
     * @param HttpHeaders $headers The headers to parse
     * @return IImmutableDictionary The mapping of cookie names to values
     */
    public function parseCookies(HttpHeaders $headers) : IImmutableDictionary
    {
        return $this->parseParameters($headers, 'Cookie');
    }
}
