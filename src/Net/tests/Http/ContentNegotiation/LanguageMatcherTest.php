<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/net/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http\Formatting;

use Aphiria\Net\Http\ContentNegotiation\LanguageMatcher;
use Aphiria\Net\Http\Headers\AcceptLanguageHeaderValue;
use Opulence\Collections\ImmutableHashTable;
use Opulence\Collections\KeyValuePair;
use PHPUnit\Framework\TestCase;

/**
 * Tests the language matcher
 */
class LanguageMatcherTest extends TestCase
{
    private LanguageMatcher $matcher;

    protected function setUp(): void
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
