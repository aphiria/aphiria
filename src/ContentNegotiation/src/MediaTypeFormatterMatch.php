<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ContentNegotiation;

use Aphiria\ContentNegotiation\MediaTypeFormatters\IMediaTypeFormatter;
use Aphiria\Net\Http\Headers\MediaTypeHeaderValue;

/**
 * Defines a media type formatter match
 */
final class MediaTypeFormatterMatch
{
    /**
     * @param IMediaTypeFormatter $formatter The matched media type formatter
     * @param string $mediaType The matched media type, eg 'application/json'
     * @param MediaTypeHeaderValue $mediaTypeHeaderValue The matched media type header value, eg 'Accept: application/json'
     */
    public function __construct(
        private IMediaTypeFormatter $formatter,
        private string $mediaType,
        private MediaTypeHeaderValue $mediaTypeHeaderValue
    ) {
    }

    /**
     * Gets the matched media type formatter
     *
     * @return IMediaTypeFormatter The matched media type formatter
     */
    public function getFormatter(): IMediaTypeFormatter
    {
        return $this->formatter;
    }

    /**
     * Gets the matched media type
     *
     * @return string The matched media type
     */
    public function getMediaType(): string
    {
        return $this->mediaType;
    }

    /**
     * Gets the matched media type header value
     *
     * @return MediaTypeHeaderValue The matched media type header value
     */
    public function getMediaTypeHeaderValue(): MediaTypeHeaderValue
    {
        return $this->mediaTypeHeaderValue;
    }
}
