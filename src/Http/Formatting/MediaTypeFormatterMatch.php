<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting;

use Opulence\Net\Http\Headers\MediaTypeHeaderValue;

/**
 * Defines a media type formatter match
 */
class MediaTypeFormatterMatch
{
    /** @var IMediaTypeFormatter The matched media type formatter */
    private $formatter;
    /** @var string|null The matched media type header, or null if no media type was specified */
    private $mediaTypeHeaderValue;

    /**
     * @param IMediaTypeFormatter $formatter The matched media type formatter
     * @param MediaTypeHeaderValue|null $mediaTypeHeaderValue The matched media type header value if there was one, otherwise null
     */
    public function __construct(IMediaTypeFormatter $formatter, ?MediaTypeHeaderValue $mediaTypeHeaderValue)
    {
        $this->formatter = $formatter;
        $this->mediaTypeHeaderValue = $mediaTypeHeaderValue;
    }

    /**
     * Gets the matched media type formatter
     *
     * @return IMediaTypeFormatter The matched media type formatter
     */
    public function getFormatter() : IMediaTypeFormatter
    {
        return $this->formatter;
    }

    /**
     * Gets the matched media type header value
     *
     * @return MediaTypeHeaderValue|null The matched media type header value if there was one, otherwise null
     */
    public function getMediaTypeHeaderValue() : ?MediaTypeHeaderValue
    {
        return $this->mediaTypeHeaderValue;
    }
}
