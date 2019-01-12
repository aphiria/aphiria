<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting;

use Opulence\Collections\ImmutableHashTable;
use Opulence\Collections\KeyValuePair;
use Opulence\Net\Http\ContentNegotiation\LanguageMatcher;
use Opulence\Net\Http\Headers\AcceptLanguageHeaderValue;
use PHPUnit\Framework\TestCase;

/**
 * Tests the language matcher
 */
class LanguageMatcherTest extends TestCase
{
    /** @var LanguageMatcher The language matcher to use in tests */
    private $matcher;

    public function setUp(): void
    {
        $this->matcher = new LanguageMatcher();
    }

    public function testLanguageIsNullWhenNoMatchesAreFound(): void
    {
        $acceptLanguageHeaders = [
            new AcceptLanguageHeaderValue('en-US', new ImmutableHashTable([new KeyValuePair('q', 1)]))
        ];
        $this->assertNull($this->matcher->getBestLanguageMatch([], $acceptLanguageHeaders));
        $this->assertNull($this->matcher->getBestLanguageMatch(['en-GB'], $acceptLanguageHeaders));
    }

    public function testLanguagesAreRankedInOrderOfQualityScore(): void
    {
        $acceptLanguageHeaders = [
            new AcceptLanguageHeaderValue('en-US', new ImmutableHashTable([new KeyValuePair('q', 0.1)])),
            new AcceptLanguageHeaderValue('en-GB', new ImmutableHashTable([new KeyValuePair('q', 0.5)]))
        ];
        $this->assertEquals('en-GB', $this->matcher->getBestLanguageMatch(['en-US', 'en-GB'], $acceptLanguageHeaders));
    }

    public function testLanguageWithWildcardIsRankedAfterLanguagesWithEqualQualityScore(): void
    {
        $acceptLanguageHeaders = [
            new AcceptLanguageHeaderValue('en-US', new ImmutableHashTable([new KeyValuePair('q', 1)])),
            new AcceptLanguageHeaderValue('*', new ImmutableHashTable([new KeyValuePair('q', 1)])),
            new AcceptLanguageHeaderValue('en-GB', new ImmutableHashTable([new KeyValuePair('q', 1)]))
        ];
        $this->assertEquals('en-US', $this->matcher->getBestLanguageMatch(['en-GB', 'en-US'], $acceptLanguageHeaders));
    }

    public function testLanguageWithZeroQualityScoreIsExcluded(): void
    {
        $acceptLanguageHeaders = [
            new AcceptLanguageHeaderValue('en-US', new ImmutableHashTable([new KeyValuePair('q', 1)])),
            new AcceptLanguageHeaderValue('en-GB', new ImmutableHashTable([new KeyValuePair('q', 0)]))
        ];
        $this->assertNull($this->matcher->getBestLanguageMatch(['en-GB'], $acceptLanguageHeaders));
    }

    public function testTruncatedLanguageCanMatchSupportedLanguage(): void
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

    public function testWildcardWithHighestScoreMatchesFirstSupportedLanguage(): void
    {
        $acceptLanguageHeaders = [
            new AcceptLanguageHeaderValue('*', new ImmutableHashTable([new KeyValuePair('q', 1)])),
            new AcceptLanguageHeaderValue('en-GB', new ImmutableHashTable([new KeyValuePair('q', 0.5)]))
        ];
        $this->assertEquals('de-DE', $this->matcher->getBestLanguageMatch(['de-DE'], $acceptLanguageHeaders));
    }
}
