<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ContentNegotiation;

use Aphiria\ContentNegotiation\MediaTypeFormatters\IMediaTypeFormatter;
use Aphiria\Net\Http\Headers\MediaTypeHeaderValue;

/**
 * Defines a media type formatter match
 */
final readonly class MediaTypeFormatterMatch
{
    /**
     * @param IMediaTypeFormatter $formatter The matched media type formatter
     * @param string $mediaType The matched media type, eg 'application/json'
     * @param MediaTypeHeaderValue $mediaTypeHeaderValue The matched media type header value, eg 'Accept: application/json'
     */
    public function __construct(
        public IMediaTypeFormatter $formatter,
        public string $mediaType,
        public MediaTypeHeaderValue $mediaTypeHeaderValue
    ) {
    }
}
