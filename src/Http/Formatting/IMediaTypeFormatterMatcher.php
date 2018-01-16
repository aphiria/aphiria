<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting;

use InvalidArgumentException;
use Opulence\Net\Http\HttpHeaders;

/**
 * Defines the interface for media type formatter matchers to implement
 */
interface IMediaTypeFormatterMatcher
{
    /**
     * Finds the media type formatter that can be used to read the body of a request
     *
     * @param HttpHeaders $requestHeaders The request headers to match on
     * @return MediaTypeFormatterMatch|null The matching formatter if found, otherwise null
     * @throws InvalidArgumentException Thrown if the Content-Type header was incorrectly formatted
     */
    public function matchReadMediaTypeFormatter(HttpHeaders $requestHeaders) : ?MediaTypeFormatterMatch;

    /**
     * Finds the media type formatter that can be used to write the body of a response
     *
     * @param HttpHeaders $requestHeaders The request headers to match on
     * @return MediaTypeFormatterMatch|null The matching formatter if found, otherwise null
     * @throws InvalidArgumentException Thrown if the Accept header's media types were incorrectly formatted
     */
    public function matchWriteMediaTypeFormatter(HttpHeaders $requestHeaders) : ?MediaTypeFormatterMatch;
}
