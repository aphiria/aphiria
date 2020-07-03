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

use Aphiria\ContentNegotiation\MediaTypeFormatters\IMediaTypeFormatter;
use Aphiria\Net\Http\Headers\MediaTypeHeaderValue;

/**
 * Defines a media type formatter match
 */
final class MediaTypeFormatterMatch
{
    /** @var IMediaTypeFormatter The matched media type formatter */
    private IMediaTypeFormatter $formatter;
    /** @var string The matched media type */
    private string $mediaType;
    /** @var MediaTypeHeaderValue The matched media type header value */
    private MediaTypeHeaderValue $mediaTypeHeaderValue;

    /**
     * @param IMediaTypeFormatter $formatter The matched media type formatter
     * @param string $mediaType The matched media type, eg 'application/json'
     * @param MediaTypeHeaderValue $mediaTypeHeaderValue The matched media type header value, eg 'Accept: application/json'
     */
    public function __construct(IMediaTypeFormatter $formatter, string $mediaType, MediaTypeHeaderValue $mediaTypeHeaderValue)
    {
        $this->formatter = $formatter;
        $this->mediaType = $mediaType;
        $this->mediaTypeHeaderValue = $mediaTypeHeaderValue;
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
