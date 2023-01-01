<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ContentNegotiation;

use Aphiria\Net\Http\Headers\MediaTypeHeaderValue;
use Aphiria\Net\Http\IRequest;

/**
 * Defines the interface for character encoding matchers to implement
 */
interface IEncodingMatcher
{
    /**
     * Gets the best character encoding match for the input media type formatter
     *
     * @param list<string> $supportedEncodings The list of supported encodings
     * @param IRequest $request The current request
     * @param MediaTypeHeaderValue|null $matchedMediaTypeHeaderValue The matched media type header value to try to extract an encoding from
     * @return string|null The best charset if one was found, otherwise null
     */
    public function getBestEncodingMatch(
        array $supportedEncodings,
        IRequest $request,
        MediaTypeHeaderValue $matchedMediaTypeHeaderValue = null
    ): ?string;
}
