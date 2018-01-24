<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting;

/**
 * Defines the results of content negotiation
 */
class ContentNegotiationResult
{
    /** @var IMediaTypeFormatter The matched media type formatter */
    private $formatter;
    /** @var string|null The matched media type, or null if no media type was specified */
    private $mediaType;
    /** @var string|null The matched charset, or null if no charset was specified */
    private $charset;

    /**
     * @param IMediaTypeFormatter $formatter The matched media type formatter
     * @param string|null $mediaType The matched media type if there was one, otherwise null
     * @param string|null $charset The matched charset if there was one, otherwise null
     */
    public function __construct(IMediaTypeFormatter $formatter, ?string $mediaType, ?string $charset)
    {
        $this->formatter = $formatter;
        $this->mediaType = $mediaType;
        $this->charset = $charset;
    }

    /**
     * Gets the matched charset
     *
     * @return string|null The matched charset if there was one, otherwise null
     */
    public function getCharset() : ?string
    {
        return $this->charset;
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
     * Gets the matched media type
     *
     * @return string|null The matched media type if there was one, otherwise null
     */
    public function getMediaType() : ?string
    {
        return $this->mediaType;
    }
}
