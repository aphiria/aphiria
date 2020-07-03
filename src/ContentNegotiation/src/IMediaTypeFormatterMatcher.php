<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ContentNegotiation;

use Aphiria\Net\Http\IRequest;

/**
 * Defines the interface for media type formatter matchers to implement
 */
interface IMediaTypeFormatterMatcher
{
    /**
     * Gets the best media type formatter match for requests
     *
     * @param string $type The type that will be read by the formatter
     * @param IRequest $request The current request
     * @return MediaTypeFormatterMatch|null The media type formatter match if there was one, otherwise null
     */
    public function getBestRequestMediaTypeFormatterMatch(string $type, IRequest $request): ?MediaTypeFormatterMatch;

    /**
     * Gets the best media type formatter match for requests
     *
     * @param string $type The type that will be written by the formatter
     * @param IRequest $request The current request
     * @return MediaTypeFormatterMatch|null The media type formatter match if there was one, otherwise null
     */
    public function getBestResponseMediaTypeFormatterMatch(string $type, IRequest $request): ?MediaTypeFormatterMatch;
}
