<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

namespace Aphiria\Net\Http\ContentNegotiation;

use Aphiria\Net\Http\Headers\MediaTypeHeaderValue;
use Aphiria\Net\Http\IHttpRequestMessage;

/**
 * Defines the interface for character encoding matchers to implement
 */
interface IEncodingMatcher
{
    /**
     * Gets the best character encoding match for the input media type formatter
     *
     * @param string[] $supportedEncodings The list of supported encodings
     * @param IHttpRequestMessage $request The current request
     * @param MediaTypeHeaderValue|null $matchedMediaTypeHeaderValue The matched media type header value to try to extract an encoding from
     * @return string|null The best charset if one was found, otherwise null
     */
    public function getBestEncodingMatch(
        array $supportedEncodings,
        IHttpRequestMessage $request,
        MediaTypeHeaderValue $matchedMediaTypeHeaderValue = null
    ): ?string;
}
