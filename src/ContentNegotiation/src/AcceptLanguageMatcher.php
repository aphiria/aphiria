<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ContentNegotiation;

use Aphiria\Net\Http\Formatting\RequestHeaderParser;
use Aphiria\Net\Http\Headers\AcceptLanguageHeaderValue;
use Aphiria\Net\Http\Headers\IHeaderValueWithQualityScore;
use Aphiria\Net\Http\IRequest;

/**
 * Defines the language matcher that looks at the accept language header
 */
final class AcceptLanguageMatcher implements ILanguageMatcher
{
    /**
     * @param list<string> $supportedLanguages The list of supported languages
     * @param RequestHeaderParser $headerParser The header parser to use to get language headers
     */
    public function __construct(
        private readonly array $supportedLanguages,
        private readonly RequestHeaderParser $headerParser = new RequestHeaderParser()
    ) {
    }

    /**
     * @inheritdoc
     */
    public function getBestLanguageMatch(IRequest $request): ?string
    {
        $acceptLanguageHeaders = $this->headerParser->parseAcceptLanguageHeader($request->getHeaders());

        if (\count($acceptLanguageHeaders) === 0) {
            return null;
        }

        \usort($acceptLanguageHeaders, [$this, 'compareAcceptLanguageHeaders']);
        $rankedAcceptLanguageHeaders = \array_values(\array_filter($acceptLanguageHeaders, [$this, 'filterZeroScores']));
        $rankedAcceptLanguageHeaderValues = $this->getLanguageValuesFromHeaders($rankedAcceptLanguageHeaders);

        foreach ($rankedAcceptLanguageHeaderValues as $language) {
            $languageParts = \explode('-', $language);

            // Progressively truncate this language tag and try to match a supported language
            do {
                foreach ($this->supportedLanguages as $supportedLanguage) {
                    if ($language === '*' || \implode('-', $languageParts) === $supportedLanguage) {
                        return $supportedLanguage;
                    }
                }

                \array_pop($languageParts);
            } while (\count($languageParts) > 0);
        }

        return null;
    }

    /**
     * Compares two languages and returns which of them is "lower" than the other
     *
     * @param AcceptLanguageHeaderValue $a The first language header to compare
     * @param AcceptLanguageHeaderValue $b The second language header to compare
     * @return int -1 if $a is lower than $b, 0 if they're even, or 1 if $a is higher than $b
     */
    private function compareAcceptLanguageHeaders(AcceptLanguageHeaderValue $a, AcceptLanguageHeaderValue $b): int
    {
        $aQuality = $a->getQuality();
        $bQuality = $b->getQuality();

        if ($aQuality < $bQuality) {
            return 1;
        }

        if ($aQuality > $bQuality) {
            return -1;
        }

        $aValue = $a->language;
        $bValue = $b->language;

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
     * Gets the language values from a list of headers
     *
     * @param list<AcceptLanguageHeaderValue> $headers The list of language headers
     * @return list<string> The list of language values from the headers
     */
    private function getLanguageValuesFromHeaders(array $headers): array
    {
        $languages = [];

        foreach ($headers as $header) {
            $languages[] = $header->language;
        }

        return $languages;
    }
}
