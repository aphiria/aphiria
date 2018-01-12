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
 * Defines the media type header ranker
 */
class MediaTypeHeaderRanker
{
    /**
     * Ranks the media type headers by quality, and then by specificity
     *
     * @param MediaTypeHeaderValue[] $mediaTypeHeaders The list of media type headers to rank
     * @return MediaTypeHeaderValue[] The ranked list of media type headers
     */
    public function rankMediaTypeHeaders(array $mediaTypeHeaders) : array
    {
        usort($mediaTypeHeaders, [$this, 'compareMediaTypes']);
        $rankedMediaTypeHeaders = array_filter($mediaTypeHeaders, [$this, 'filterZeroScores']);

        // Have to return the values because the keys aren't updated in array_filter
        return array_values($rankedMediaTypeHeaders);
    }

    /**
     * Compares two media types and returns which of them is "lower" than the other
     *
     * @param MediaTypeHeaderValue $a The first media type to compare
     * @param MediaTypeHeaderValue $b The second media type to compare
     * @return int -1 if $a is lower than $b, 0 if they're even, or 1 if $a is higher than $b
     */
    private function compareMediaTypes(MediaTypeHeaderValue $a, MediaTypeHeaderValue $b) : int
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
    private function filterZeroScores(MediaTypeHeaderValue $mediaTypeHeaderValue) : bool
    {
        return $mediaTypeHeaderValue->getQuality() > 0;
    }
}
