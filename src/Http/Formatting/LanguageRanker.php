<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting;

use Opulence\Net\Http\Headers\AcceptLanguageHeaderValue;
use Opulence\Net\Http\Headers\IHeaderValueWithQualityScore;

/**
 * Defines the language ranker
 */
class LanguageRanker
{
    /**
     * Ranks the language headers by quality, and then by specificity
     *
     * @param AcceptLanguageHeaderValue[] $languageHeaders The list of language headers to rank
     * @return AcceptLanguageHeaderValue[] The ranked list of language headers
     */
    public function rankAcceptLanguageHeaders(array $languageHeaders) : array
    {
        usort($languageHeaders, [$this, 'compareAcceptLanguageHeaders']);
        $rankedLanguageHeaders = array_filter($languageHeaders, [$this, 'filterZeroScores']);

        return $this->getLanguageValuesFromHeaders($rankedLanguageHeaders);
    }

    /**
     * Compares two languages and returns which of them is "lower" than the other
     *
     * @param AcceptLanguageHeaderValue $a The first language header to compare
     * @param AcceptLanguageHeaderValue $b The second language header to compare
     * @return int -1 if $a is lower than $b, 0 if they're even, or 1 if $a is higher than $b
     */
    private function compareAcceptLanguageHeaders(AcceptLanguageHeaderValue $a, AcceptLanguageHeaderValue $b) : int
    {
        $aQuality = $a->getQuality();
        $bQuality = $b->getQuality();

        if ($aQuality < $bQuality) {
            return 1;
        }

        if ($aQuality > $bQuality) {
            return -1;
        }

        $aValue = $a->getLanguage();
        $bValue = $b->getLanguage();

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
    private function filterZeroScores(IHeaderValueWithQualityScore $header) : bool
    {
        return $header->getQuality() > 0;
    }

    /**
     * Gets the language values from a list of headers
     *
     * @param AcceptLanguageHeaderValue $headers The list of language headers
     * @return array The list of language values from the headers
     */
    private function getLanguageValuesFromHeaders(array $headers) : array
    {
        $languages = [];

        foreach ($headers as $header) {
            $languages[] = $header->getLanguage();
        }

        return $languages;
    }
}
