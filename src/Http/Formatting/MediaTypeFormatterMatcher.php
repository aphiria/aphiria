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
use Opulence\Net\Http\HttpHeaders;

/**
 * Defines the media type formatter matcher
 */
class MediaTypeFormatterMatcher implements IMediaTypeFormatterMatcher
{
    /** @var IMediaTypeFormatter[] The list of registered formatters */
    private $formatters;
    /** @var HttpHeaderParser The header parser */
    private $headerParser;
    /** @var MediaTypeHeaderRanker The media type header ranker */
    private $mediaTypeHeaderRanker;

    /**
     * @param IMediaTypeFormatter[] $formatters The list of formatters
     * @param HttpHeaderParser $headerParser The header parser
     * @param MediaTypeHeaderRanker $mediaTypeHeaderRanker The media type header ranker
     * @throws InvalidArgumentException Thrown if the list of formatters is empty
     */
    public function __construct(
        array $formatters,
        HttpHeaderParser $headerParser,
        MediaTypeHeaderRanker $mediaTypeHeaderRanker
    ) {
        if (count($formatters) === 0) {
            throw new InvalidArgumentException('List of formatters must not be empty');
        }

        $this->formatters = $formatters;
        $this->headerParser = $headerParser;
        $this->mediaTypeHeaderRanker = $mediaTypeHeaderRanker;
    }

    /**
     * @inheritdoc
     */
    public function matchReadMediaTypeFormatter(HttpHeaders $requestHeaders) : ?MediaTypeFormatterMatch
    {
        $contentType = null;

        if (!$requestHeaders->tryGetFirst('Content-Type', $contentType)) {
            // Default to the first registered media type formatter
            return new MediaTypeFormatterMatch(
                $this->formatters[0],
                null
            );
        }

        $formatterMatch = $this->getFirstMediaTypeFormatterMatch($contentType);

        if ($formatterMatch !== null) {
            return $formatterMatch;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function matchWriteMediaTypeFormatter(HttpHeaders $requestHeaders) : ?MediaTypeFormatterMatch
    {
        if (!$requestHeaders->has('Accept')) {
            // Default to the first registered media type formatter
            return new MediaTypeFormatterMatch(
                $this->formatters[0],
                null
            );
        }

        $mediaTypeHeaders = $this->headerParser->parseAcceptParameters($requestHeaders);
        $rankedMediaTypeHeaders = $this->mediaTypeHeaderRanker->rankMediaTypeHeaders($mediaTypeHeaders);

        foreach ($rankedMediaTypeHeaders as $mediaTypeHeader) {
            $formatterMatch = $this->getFirstMediaTypeFormatterMatch($mediaTypeHeader->getMediaType());

            if ($formatterMatch !== null) {
                return $formatterMatch;
            }
        }

        return null;
    }

    /**
     * Gets the first matching media type formatter
     *
     * @param string $mediaType The media type to match on
     * @return MediaTypeFormatterMatch|null The matching formatter if one was found, otherwise null
     * @throws InvalidArgumentException Thrown if the media type was incorrectly formatted
     */
    protected function getFirstMediaTypeFormatterMatch(string $mediaType) : MediaTypeFormatterMatch
    {
        $mediaTypeParts = explode('/', $mediaType);

        // Don't bother going on if the media type isn't in the correct format
        if (count($mediaTypeParts) !== 2 || $mediaTypeParts[0] === '' || $mediaTypeParts[1] === '') {
            throw new InvalidArgumentException('Media type must be in format {type}/{sub-type}');
        }

        [$type, $subType] = $mediaTypeParts;

        foreach ($this->formatters as $formatter) {
            foreach ($formatter->getSupportedMediaTypes() as $supportedMediaType) {
                [$supportedType, $supportedSubType] = explode('/', $supportedMediaType);

                if (
                    $type === '*' ||
                    ($subType === '*' && $type === $supportedType) ||
                    ($type === $supportedType && $subType === $supportedSubType)
                ) {
                    return new MediaTypeFormatterMatch($formatter, $supportedMediaType);
                }
            }
        }

        return null;
    }
}
