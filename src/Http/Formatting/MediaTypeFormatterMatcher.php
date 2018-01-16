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

    /**
     * @param IMediaTypeFormatter[] $formatters The list of formatters
     * @param RequestHeaderParser|null $headerParser The header parser, or null if using the default one
     * @throws InvalidArgumentException Thrown if the list of formatters is empty
     */
    public function __construct(array $formatters, RequestHeaderParser $headerParser = null)
    {
        if (count($formatters) === 0) {
            throw new InvalidArgumentException('List of formatters must not be empty');
        }

        $this->formatters = $formatters;
        $this->headerParser = $headerParser ?? new RequestHeaderParser();
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
        if (!$requestHeaders->containsKey('Accept')) {
            // Default to the first registered media type formatter
            return new MediaTypeFormatterMatch(
                $this->formatters[0],
                null
            );
        }

        $mediaTypeHeaders = $this->headerParser->parseAcceptParameters($requestHeaders);
        $rankedMediaTypeHeaders = $this->rankMediaTypeHeaders($mediaTypeHeaders);

        foreach ($rankedMediaTypeHeaders as $mediaTypeHeader) {
            $formatterMatch = $this->getFirstMediaTypeFormatterMatch($mediaTypeHeader->getFullMediaType());

            if ($formatterMatch !== null) {
                return $formatterMatch;
            }
        }

        return null;
    }

    /**
     * Compares two media types and returns which of them is "lower" than the other
     *
     * @param MediaTypeHeaderValue $a The first media type to compare
     * @param MediaTypeHeaderValue $b The second media type to compare
     * @return int -1 if $a is lower than $b, 0 if they're even, or 1 if $a is higher than $b
     */
    protected function compareMediaTypes(MediaTypeHeaderValue $a, MediaTypeHeaderValue $b) : int
    {
        $aQuality = $a->getQuality();
        $bQuality = $b->getQuality();

        if ($aQuality < $bQuality) {
            return 1;
        }

        if ($aQuality > $bQuality) {
            return -1;
        }

        $aType = $a->getType();
        $bType = $b->getType();
        $aSubType = $a->getSubType();
        $bSubType = $b->getSubType();

        if ($aType === '*') {
            if ($bType === '*') {
                return 0;
            }

            return 1;
        }

        if ($aSubType === '*') {
            if ($bSubType === '*') {
                return 0;
            }

            return 1;
        }

        // If we've gotten here, then $a had no wildcards
        if ($bType === '*' || $bSubType === '*') {
            return -1;
        }

        return 0;
    }

    /**
     * Filters out any media type header values with a zero quality score
     *
     * @param MediaTypeHeaderValue $mediaTypeHeaderValue The value to check
     * @return bool True if we should keep the value, otherwise false
     */
    protected function filterZeroScores(MediaTypeHeaderValue $mediaTypeHeaderValue) : bool
    {
        return $mediaTypeHeaderValue->getQuality() > 0;
    }

    /**
     * Gets the first matching media type formatter
     *
     * @param string $mediaType The media type to match on
     * @return MediaTypeFormatterMatch|null The matching formatter if one was found, otherwise null
     * @throws InvalidArgumentException Thrown if the media type was incorrectly formatted
     */
    protected function getFirstMediaTypeFormatterMatch(string $mediaType) : ?MediaTypeFormatterMatch
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

                // Checks if the type is a wildcard or a match and the sub-type is a wildcard or a match
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

    /**
     * Ranks the media type headers by quality, and then by specificity
     *
     * @param MediaTypeHeaderValue[] $mediaTypeHeaders The list of media type headers to rank
     * @return MediaTypeHeaderValue[] The ranked list of media type headers
     */
    protected function rankMediaTypeHeaders(array $mediaTypeHeaders) : array
    {
        usort($mediaTypeHeaders, [$this, 'compareMediaTypes']);
        $rankedMediaTypeHeaders = array_filter($mediaTypeHeaders, [$this, 'filterZeroScores']);

        // Have to return the values because the keys aren't updated in array_filter
        return array_values($rankedMediaTypeHeaders);
    }
}
