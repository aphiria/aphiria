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
use Opulence\Net\Http\HttpHeaders;

/**
 * Defines the request header parser
 */
class RequestHeaderParser extends HttpHeaderParser
{
    /**
     * Parses the Accept header parameters from
     *
     * @param HttpHeaders $headers The request headers to parse
     * @return MediaTypeHeaderValue[] The list of media type header values
     */
    public function parseAcceptParameters(HttpHeaders $headers) : array
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
            $qualityScore = $parsedHeaderParameters->containsKey('q') ? (float)$parsedHeaderParameters['q'] : null;
            $parsedHeaderValues[] = new MediaTypeHeaderValue($mediaType, $qualityScore);
        }

        return $parsedHeaderValues;
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
