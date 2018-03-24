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
    /** @var string|null The matched encoding, or null if no encoding was specified */
    private $encoding;
    /** @var array The list of languages in order of preference */
    private $languages = [];

    /**
     * @param IMediaTypeFormatter $formatter The matched media type formatter
     * @param string|null $mediaType The matched media type if there was one, otherwise null
     * @param string|null $encoding The matched encoding if there was one, otherwise null
     * @param array $languages The list of languages in order of preference
     */
    public function __construct(IMediaTypeFormatter $formatter, ?string $mediaType, ?string $encoding, array $languages)
    {
        $this->formatter = $formatter;
        $this->mediaType = $mediaType;
        $this->encoding = $encoding;
        $this->languages = $languages;
    }

    /**
     * Gets the matched encoding
     *
     * @return string|null The matched encoding if there was one, otherwise null
     */
    public function getEncoding() : ?string
    {
        return $this->encoding;
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
     * Gets the list of languages in order of preference
     *
     * @return array The languages
     */
    public function getLanguages() : array
    {
        return $this->languages;
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
