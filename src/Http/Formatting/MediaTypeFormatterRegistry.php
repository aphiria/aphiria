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

/**
 * Defines the media type formatter registry
 */
class MediaTypeFormatterRegistry
{
    /** @var IMediaTypeFormatter[] The list of registered formatters */
    private $formatters = [];

    /**
     * Gets the default formatter, which is the first formatter registered
     *
     * @return IMediaTypeFormatter|null The default formatter if one exists, otherwise null
     */
    public function getDefaultFormatter() : ?IMediaTypeFormatter
    {
        return $this->formatters[0] ?? null;
    }

    /**
     * Gets a list of media type formatter matches for the input media type
     *
     * @param string $mediaType The raw media type value, eg "text/html" or "text/*"
     * @return MediaTypeFormatterMatch[] The list of media type formatter matches
     * @throws InvalidArgumentException Thrown if the media type is an invalid media type
     */
    public function getFormatterMatches(string $mediaType) : array
    {
        $mediaTypeParts = explode('/', $mediaType);

        if (count($mediaTypeParts) !== 2 || $mediaTypeParts[0] === '' || $mediaTypeParts[1] === '') {
            throw new InvalidArgumentException('Media type must be of format {type}/{sub-type}');
        }

        [$type, $subType] = $mediaTypeParts;
        $matches = [];

        foreach ($this->formatters as $formatter) {
            foreach ($formatter->getSupportedMediaTypes() as $supportedMediaType) {
                [$supportedType, $supportedSubType] = explode('/', $supportedMediaType);

                if (
                    $type === '*' ||
                    ($subType === '*' && $type === $supportedType) ||
                    ($type === $supportedType && $subType === $supportedSubType)
                ) {
                    $matches[] = new MediaTypeFormatterMatch($formatter, $supportedMediaType);
                    // We only care about the first supported media type, so proceed to the next formatter
                    continue 2;
                }
            }
        }

        return $matches;
    }

    /**
     * Registers a formatter
     *
     * @param IMediaTypeFormatter $formatter The formatter to register
     */
    public function registerFormatter(IMediaTypeFormatter $formatter) : void
    {
        $this->formatters[] = $formatter;
    }
}
