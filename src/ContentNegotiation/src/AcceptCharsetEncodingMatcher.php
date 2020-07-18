<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ContentNegotiation;

use Aphiria\Net\Http\Formatting\RequestHeaderParser;
use Aphiria\Net\Http\Headers\AcceptCharsetHeaderValue;
use Aphiria\Net\Http\Headers\IHeaderValueWithQualityScore;
use Aphiria\Net\Http\Headers\MediaTypeHeaderValue;
use Aphiria\Net\Http\IRequest;

/**
 * Defines the Accept-Charset encoding matcher
 */
final class AcceptCharsetEncodingMatcher implements IEncodingMatcher
{
    /** @var RequestHeaderParser The header parser to use to get charset headers */
    private RequestHeaderParser $headerParser;

    /**
     * @param RequestHeaderParser|null $headerParser The header parser to use to get charset headers
     */
    public function __construct(RequestHeaderParser $headerParser = null)
    {
        $this->headerParser = $headerParser ?? new RequestHeaderParser();
    }

    /**
     * @inheritdoc
     */
    public function getBestEncodingMatch(
        array $supportedEncodings,
        IRequest $request,
        MediaTypeHeaderValue $matchedMediaTypeHeaderValue = null
    ): ?string {
        $acceptCharsetHeaders = $this->headerParser->parseAcceptCharsetHeader($request->getHeaders());
        $rankedAcceptCharsetHeaders = $this->rankAcceptCharsetHeaders($acceptCharsetHeaders);

        foreach ($rankedAcceptCharsetHeaders as $acceptCharsetHeader) {
            foreach ($supportedEncodings as $supportedEncoding) {
                $charset = $acceptCharsetHeader->getCharset();

                if ($charset === '*' || strcasecmp($charset, $supportedEncoding) === 0) {
                    return $supportedEncoding;
                }
            }
        }

        if ($matchedMediaTypeHeaderValue === null || $matchedMediaTypeHeaderValue->getCharset() === null) {
            return null;
        }

        // Fall back to the charset in the media type header
        foreach ($supportedEncodings as $supportedEncoding) {
            $charset = $matchedMediaTypeHeaderValue->getCharset();

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
