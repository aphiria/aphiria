<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http\Formatting;

use Aphiria\Collections\IImmutableDictionary;
use Aphiria\Net\Http\Headers\AcceptCharsetHeaderValue;
use Aphiria\Net\Http\Headers\AcceptLanguageHeaderValue;
use Aphiria\Net\Http\Headers\AcceptMediaTypeHeaderValue;
use Aphiria\Net\Http\Headers\ContentTypeHeaderValue;
use Aphiria\Net\Http\HttpHeaders;
use InvalidArgumentException;

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
     * @throws InvalidArgumentException Thrown if the headers were incorrectly formatted
     */
    public function parseAcceptCharsetHeader(HttpHeaders $headers): array
    {
        $headerValues = [];

        if (!$headers->tryGet('Accept-Charset', $headerValues)) {
            return [];
        }

        $parsedHeaderValues = [];
        $numHeaderValues = count($headerValues);

        for ($i = 0;$i < $numHeaderValues;$i++) {
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
     * @throws InvalidArgumentException Thrown if the headers were incorrectly formatted
     */
    public function parseAcceptHeader(HttpHeaders $headers): array
    {
        $headerValues = [];

        if (!$headers->tryGet('Accept', $headerValues)) {
            return [];
        }

        $parsedHeaderValues = [];
        $numHeaderValues = count($headerValues);

        for ($i = 0;$i < $numHeaderValues;$i++) {
            $parsedHeaderParameters = $this->parseParameters($headers, 'Accept', $i);
            // The first value should always be the media type
            $mediaType = $parsedHeaderParameters->getKeys()[0];
            $parsedHeaderValues[] = new AcceptMediaTypeHeaderValue($mediaType, $parsedHeaderParameters);
        }

        return $parsedHeaderValues;
    }

    /**
     * Parses the Accept-Language header
     *
     * @param HttpHeaders $headers The request headers to parse
     * @return AcceptLanguageHeaderValue[] The list of language header values
     * @throws InvalidArgumentException Thrown if the headers were incorrectly formatted
     */
    public function parseAcceptLanguageHeader(HttpHeaders $headers): array
    {
        $headerValues = [];

        if (!$headers->tryGet('Accept-Language', $headerValues)) {
            return [];
        }

        $parsedHeaderValues = [];
        $numHeaderValues = count($headerValues);

        for ($i = 0;$i < $numHeaderValues;$i++) {
            $parsedHeaderParameters = $this->parseParameters($headers, 'Accept-Language', $i);
            // The first value should always be the language
            $language = $parsedHeaderParameters->getKeys()[0];
            $parsedHeaderValues[] = new AcceptLanguageHeaderValue($language, $parsedHeaderParameters);
        }

        return $parsedHeaderValues;
    }

    /**
     * Parses the Content-Type header
     *
     * @param HttpHeaders $headers The request headers to parse
     * @return ContentTypeHeaderValue|null The parsed header if one exists, otherwise null
     * @throws InvalidArgumentException Thrown if the headers were incorrectly formatted
     */
    public function parseContentTypeHeader(HttpHeaders $headers): ?ContentTypeHeaderValue
    {
        if (!$headers->containsKey('Content-Type')) {
            return null;
        }

        $contentTypeHeaderParameters = $this->parseParameters($headers, 'Content-Type');
        $contentType = $contentTypeHeaderParameters->getKeys()[0];

        return new ContentTypeHeaderValue($contentType, $contentTypeHeaderParameters);
    }

    /**
     * Parses the request headers for all cookie values
     *
     * @param HttpHeaders $headers The headers to parse
     * @return IImmutableDictionary The mapping of cookie names to values
     */
    public function parseCookies(HttpHeaders $headers): IImmutableDictionary
    {
        return $this->parseParameters($headers, 'Cookie');
    }
}
