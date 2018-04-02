<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting;

use Opulence\Net\Http\Headers\AcceptCharsetHeaderValue;
use Opulence\Net\Http\Headers\IHeaderValueWithQualityScore;
use Opulence\Net\Http\Headers\MediaTypeHeaderValue;

/**
 * Defines the character encoding matcher
 */
class EncodingMatcher
{
    /**
     * Gets the best character encoding match for the input media type formatter
     *
     * @param IMediaTypeFormatter $formatter The media type formatter to match against
     * @param AcceptCharsetHeaderValue[] $acceptCharsetHeaders The list of charset header values to match against
     * @param MediaTypeHeaderValue|null $mediaTypeHeader The matched media type header value if one was set, otherwise null
     * @return string|null The best charset if one was found, otherwise null
     */
    public function getBestEncodingMatch(
        IMediaTypeFormatter $formatter,
        array $acceptCharsetHeaders,
        ?MediaTypeHeaderValue $mediaTypeHeader
    ) : ?string {
        $rankedAcceptCharsetHeaders = $this->rankAcceptCharsetHeaders($acceptCharsetHeaders);

        foreach ($rankedAcceptCharsetHeaders as $acceptCharsetHeader) {
            foreach ($formatter->getSupportedEncodings() as $supportedEncoding) {
                $charset = $acceptCharsetHeader->getCharset();

                if ($charset === '*' || strcasecmp($charset, $supportedEncoding) === 0) {
                    return $supportedEncoding;
                }
            }
        }

        if ($mediaTypeHeader === null || $mediaTypeHeader->getCharset() === null) {
            return null;
        }

        // Fall back to the charset in the media type header
        foreach ($formatter->getSupportedEncodings() as $supportedEncoding) {
            $charset = $mediaTypeHeader->getCharset();

            if ($charset === '*' || strcasecmp($charset, $supportedEncoding) === 0) {
                return $supportedEncoding;
            }
        }

        return null;
    }

    /**
     * Compares two charsets and returns which of them is "lower" than the other
     *
     * @param AcceptCharsetHeaderValue $a The first charset header to compare
     * @param AcceptCharsetHeaderValue $b The second charset header to compare
     * @return int -1 if $a is lower than $b, 0 if they're even, or 1 if $a is higher than $b
     */
    private function compareAcceptCharsetHeaders(AcceptCharsetHeaderValue $a, AcceptCharsetHeaderValue $b): int
    {
        $aQuality = $a->getQuality();
        $bQuality = $b->getQuality();

        if ($aQuality < $bQuality) {
            return 1;
        }

        if ($aQuality > $bQuality) {
            return -1;
        }

        $aValue = $a->getCharset();
        $bValue = $b->getCharset();

        if ($aValue === '*') {
            if ($bValue === '*') {
                return 0;
            }

            return 1;
        }

        if ($bValue === '*') {
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
    private function filterZeroScores(IHeaderValueWithQualityScore $header): bool
    {
        return $header->getQuality() > 0;
    }

    /**
     * Ranks the charset headers by quality, and then by specificity
     *
     * @param AcceptCharsetHeaderValue[] $charsetHeaders The list of charset headers to rank
     * @return AcceptCharsetHeaderValue[] The ranked list of charset headers
     */
    private function rankAcceptCharsetHeaders(array $charsetHeaders): array
    {
        usort($charsetHeaders, [$this, 'compareAcceptCharsetHeaders']);
        $rankedCharsetHeaders = array_filter($charsetHeaders, [$this, 'filterZeroScores']);

        // Have to return the values because the keys aren't updated in array_filter()
        return array_values($rankedCharsetHeaders);
    }
}
