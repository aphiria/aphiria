<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting;

use Opulence\Collections\ImmutableHashTable;
use Opulence\Collections\KeyValuePair;
use Opulence\Net\Http\Formatting\LanguageMatcher;
use Opulence\Net\Http\Headers\AcceptLanguageHeaderValue;

/**
 * Tests the language matcher
 */
class LanguageMatcherTest extends \PHPUnit\Framework\TestCase
{
    /** @var LanguageMatcher The language matcher to use in tests */
    private $matcher;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->matcher = new LanguageMatcher();
    }

    /**
     * Tests that the first supported languages is the default when no matches are found
     */
    public function testLanguageIsNullWhenNoMatchesAreFound() : void
    {
        $acceptLanguageHeaders = [
            new AcceptLanguageHeaderValue('en-US', new ImmutableHashTable([new KeyValuePair('q', 1)]))
        ];
        $this->assertNull($this->matcher->getBestLanguageMatch([], $acceptLanguageHeaders));
        $this->assertNull($this->matcher->getBestLanguageMatch(['en-GB'], $acceptLanguageHeaders));
    }

    /**
     * Tests that the languages are chosen in order of quality score
     */
    public function testLanguagesAreRankedInOrderOfQualityScore() : void
    {
        $acceptLanguageHeaders = [
            new AcceptLanguageHeaderValue('en-US', new ImmutableHashTable([new KeyValuePair('q', 0.1)])),
            new AcceptLanguageHeaderValue('en-GB', new ImmutableHashTable([new KeyValuePair('q', 0.5)]))
        ];
        $this->assertEquals('en-GB', $this->matcher->getBestLanguageMatch(['en-US', 'en-GB'], $acceptLanguageHeaders));
    }

    /**
     * Tests that a language with a wildcard is ranked after languages with an equal quality score
     */
    public function testLanguageWithWildcardIsRankedAfterLanguagesWithEqualQualityScore() : void
    {
        $acceptLanguageHeaders = [
            new AcceptLanguageHeaderValue('en-US', new ImmutableHashTable([new KeyValuePair('q', 1)])),
            new AcceptLanguageHeaderValue('*', new ImmutableHashTable([new KeyValuePair('q', 1)])),
            new AcceptLanguageHeaderValue('en-GB', new ImmutableHashTable([new KeyValuePair('q', 1)]))
        ];
        $this->assertEquals('en-US', $this->matcher->getBestLanguageMatch(['en-GB', 'en-US'], $acceptLanguageHeaders));
    }

    /**
     * Tests that a language with a zero quality score is excluded
     */
    public function testLanguageWithZeroQualityScoreIsExcluded() : void
    {
        $acceptLanguageHeaders = [
            new AcceptLanguageHeaderValue('en-US', new ImmutableHashTable([new KeyValuePair('q', 1)])),
            new AcceptLanguageHeaderValue('en-GB', new ImmutableHashTable([new KeyValuePair('q', 0)]))
        ];
        $this->assertNull($this->matcher->getBestLanguageMatch(['en-GB'], $acceptLanguageHeaders));
    }

    /**
     * Tests that a truncated language can match a supported language
     */
    public function testTruncatedLanguageCanMatchSupportedLanguage() : void
    {
        $acceptLanguageHeaders = [
            new AcceptLanguageHeaderValue('en-US-123', new ImmutableHashTable([new KeyValuePair('q', 1)]))
        ];
        $this->assertEquals('en-US', $this->matcher->getBestLanguageMatch(['en-US'], $acceptLanguageHeaders));
        $acceptLanguageHeaders = [
            new AcceptLanguageHeaderValue('en-US', new ImmutableHashTable([new KeyValuePair('q', 1)]))
        ];
        $this->assertEquals('en', $this->matcher->getBestLanguageMatch(['en'], $acceptLanguageHeaders));
    }

    /**
     * Tests that a wildcard language with the highest score matches the first supported language
     */
    public function testWildcardWithHighestScoreMatchesFirstSupportedLanguage() : void
    {
        $acceptLanguageHeaders = [
            new AcceptLanguageHeaderValue('*', new ImmutableHashTable([new KeyValuePair('q', 1)])),
            new AcceptLanguageHeaderValue('en-GB', new ImmutableHashTable([new KeyValuePair('q', 0.5)]))
        ];
        $this->assertEquals('de-DE', $this->matcher->getBestLanguageMatch(['de-DE'], $acceptLanguageHeaders));
    }
}
