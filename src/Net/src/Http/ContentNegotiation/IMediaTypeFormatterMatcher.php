<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

namespace Aphiria\Net\Http\ContentNegotiation;


use Aphiria\Net\Http\HttpHeaders;

/**
 * Defines the interface for media type formatter matchers to implement
 */
interface IMediaTypeFormatterMatcher
{
    /**
     * Gets the best media type formatter match for requests
     *
     * @param string $type The type that will be read by the formatter
     * @param HttpHeaders $requestHeaders The headers to match against
     * @return MediaTypeFormatterMatch|null The media type formatter match if there was one, otherwise null
     */
    public function getBestRequestMediaTypeFormatterMatch(string $type, HttpHeaders $requestHeaders): ?MediaTypeFormatterMatch;

    /**
     * Gets the best media type formatter match for requests
     *
     * @param string $type The type that will be written by the formatter
     * @param HttpHeaders $requestHeaders The request headers
     * @return MediaTypeFormatterMatch|null The media type formatter match if there was one, otherwise null
     */
    public function getBestResponseMediaTypeFormatterMatch(string $type, HttpHeaders $requestHeaders): ?MediaTypeFormatterMatch;
}
