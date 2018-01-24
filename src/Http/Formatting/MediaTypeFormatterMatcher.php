<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting;

use Opulence\Net\Http\Headers\AcceptMediaTypeHeaderValue;
use Opulence\Net\Http\Headers\IHeaderValueWithQualityScore;
use Opulence\Net\Http\Headers\MediaTypeHeaderValue;

/**
 * Defines the media type formatter matcher
 */
class MediaTypeFormatterMatcher
{
    /**
     * Gets the best media type formatter match
     *
     * @param IMediaTypeFormatter[] $formatters The list of formatters to match against
     * @param MediaTypeHeaderValue[] $mediaTypeHeaders The media type headers to match against
     * @return MediaTypeFormatterMatch|null The media type formatter match if there was one, otherwise null
     */
    public function getBestMediaTypeFormatterMatch(array $formatters, array $mediaTypeHeaders) : ?MediaTypeFormatterMatch
    {
        // Rank the media type headers if they are rankable
        if (count($mediaTypeHeaders) > 0 && $mediaTypeHeaders[0] instanceof IHeaderValueWithQualityScore) {
            $mediaTypeHeaders = $this->rankAcceptMediaTypeHeaders($mediaTypeHeaders);
        }

        foreach ($mediaTypeHeaders as $mediaTypeHeader) {
            $mediaTypeParts = explode('/', $mediaTypeHeader->getMediaType());

            // Don't bother going on if the media type isn't in the correct format
            if (count($mediaTypeParts) !== 2 || $mediaTypeParts[0] === '' || $mediaTypeParts[1] === '') {
                throw new InvalidArgumentException('Media type must be in format {type}/{sub-type}');
            }

            [$type, $subType] = $mediaTypeParts;

            foreach ($formatters as $formatter) {
                foreach ($formatter->getSupportedMediaTypes() as $supportedMediaType) {
                    [$supportedType, $supportedSubType] = explode('/', $supportedMediaType);

                    // Checks if the type is a wildcard or a match and the sub-type is a wildcard or a match
                    if (
                        $type === '*' ||
                        ($subType === '*' && $type === $supportedType) ||
                        ($type === $supportedType && $subType === $supportedSubType)
                    ) {
                        return new MediaTypeFormatterMatch($formatter, $supportedMediaType, $mediaTypeHeader);
                    }
                }
            }
        }

        return null;
    }

    /**
     * Compares two media types and returns which of them is "lower" than the other
     *
     * @param AcceptMediaTypeHeaderValue $a The first media type to compare
     * @param AcceptMediaTypeHeaderValue $b The second media type to compare
     * @return int -1 if $a is lower than $b, 0 if they're even, or 1 if $a is higher than $b
     */
    private function compareMediaTypes(AcceptMediaTypeHeaderValue $a, AcceptMediaTypeHeaderValue $b) : int
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
     * Filters out any header values with a zero quality score
     *
     * @param IHeaderValueWithQualityScore $header The value to check
     * @return bool True if we should keep the value, otherwise false
     */
    private function filterZeroScores(IHeaderValueWithQualityScore $header) : bool
    {
        return $header->getQuality() > 0;
    }

    /**
     * Ranks the media type headers by quality, and then by specificity
     *
     * @param AcceptMediaTypeHeaderValue[] $mediaTypeHeaders The list of media type headers to rank
     * @return AcceptMediaTypeHeaderValue[] The ranked list of media type headers
     */
    private function rankAcceptMediaTypeHeaders(array $mediaTypeHeaders) : array
    {
        usort($mediaTypeHeaders, [$this, 'compareMediaTypes']);
        $rankedMediaTypeHeaders = array_filter($mediaTypeHeaders, [$this, 'filterZeroScores']);

        // Have to return the values because the keys aren't updated in array_filter()
        return array_values($rankedMediaTypeHeaders);
    }
}
