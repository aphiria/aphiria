<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http\Formatting;

use Aphiria\Collections\IImmutableDictionary;
use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\Headers\AcceptCharsetHeaderValue;
use Aphiria\Net\Http\Headers\AcceptLanguageHeaderValue;
use Aphiria\Net\Http\Headers\AcceptMediaTypeHeaderValue;
use InvalidArgumentException;

/**
 * Defines the request header parser
 */
class RequestHeaderParser extends HeaderParser
{
    /**
     * Parses the Accept-Charset header
     *
     * @param Headers $headers The request headers to parse
     * @return AcceptCharsetHeaderValue[] The list of charset header values
     * @throws InvalidArgumentException Thrown if the headers were incorrectly formatted
     */
    public function parseAcceptCharsetHeader(Headers $headers): array
    {
        $headerValues = [];

        if (!$headers->tryGet('Accept-Charset', $headerValues)) {
            return [];
        }

        $parsedHeaderValues = [];
        /** @var array<string, string|string[]|int|float> $headerValues */
        $numHeaderValues = \count($headerValues);

        for ($i = 0;$i < $numHeaderValues;$i++) {
            $parsedHeaderParameters = $this->parseParameters($headers, 'Accept-Charset', $i);
            // The first value should always be the charset
            $charset = (string)$parsedHeaderParameters->getKeys()[0];
            $parsedHeaderValues[] = new AcceptCharsetHeaderValue($charset, $parsedHeaderParameters);
        }

        return $parsedHeaderValues;
    }

    /**
     * Parses the Accept header
     *
     * @param Headers $headers The request headers to parse
     * @return AcceptMediaTypeHeaderValue[] The list of media type header values
     * @throws InvalidArgumentException Thrown if the headers were incorrectly formatted
     */
    public function parseAcceptHeader(Headers $headers): array
    {
        $headerValues = [];

        if (!$headers->tryGet('Accept', $headerValues)) {
            return [];
        }

        $parsedHeaderValues = [];
        /** @var array<string, string|string[]|int|float> $headerValues */
        $numHeaderValues = \count($headerValues);

        for ($i = 0;$i < $numHeaderValues;$i++) {
            $parsedHeaderParameters = $this->parseParameters($headers, 'Accept', $i);
            // The first value should always be the media type
            $mediaType = (string)$parsedHeaderParameters->getKeys()[0];
            $parsedHeaderValues[] = new AcceptMediaTypeHeaderValue($mediaType, $parsedHeaderParameters);
        }

        return $parsedHeaderValues;
    }

    /**
     * Parses the Accept-Language header
     *
     * @param Headers $headers The request headers to parse
     * @return AcceptLanguageHeaderValue[] The list of language header values
     * @throws InvalidArgumentException Thrown if the headers were incorrectly formatted
     */
    public function parseAcceptLanguageHeader(Headers $headers): array
    {
        $headerValues = [];

        if (!$headers->tryGet('Accept-Language', $headerValues)) {
            return [];
        }

        $parsedHeaderValues = [];
        /** @var array<string, string|string[]|int|float> $headerValues */
        $numHeaderValues = \count($headerValues);

        for ($i = 0;$i < $numHeaderValues;$i++) {
            $parsedHeaderParameters = $this->parseParameters($headers, 'Accept-Language', $i);
            // The first value should always be the language
            $language = (string)$parsedHeaderParameters->getKeys()[0];
            $parsedHeaderValues[] = new AcceptLanguageHeaderValue($language, $parsedHeaderParameters);
        }

        return $parsedHeaderValues;
    }

    /**
     * Parses the request headers for all cookie values
     *
     * @param Headers $headers The headers to parse
     * @return IImmutableDictionary The mapping of cookie names to values
     */
    public function parseCookies(Headers $headers): IImmutableDictionary
    {
        return $this->parseParameters($headers, 'Cookie');
    }
}
