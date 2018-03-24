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
use Opulence\Net\Http\Formatting\LanguageRanker;
use Opulence\Net\Http\Headers\AcceptLanguageHeaderValue;

/**
 * Tests the language ranker
 */
class LanguageRankerTest extends \PHPUnit\Framework\TestCase
{
    /** @var LanguageRanker The language ranker to use in tests */
    private $ranker;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->ranker = new LanguageRanker();
    }

    /**
     * Tests that the languages are chosen in order of quality score
     */
    public function testLanguagesAreRankedInOrderOfQualityScore() : void
    {
        $acceptLanguageHeaders = [
            new AcceptLanguageHeaderValue('en-US', new ImmutableHashTable([new KeyValuePair('q', 0.1)])),
            new AcceptLanguageHeaderValue('en-GB', new ImmutableHashTable([new KeyValuePair('q', 0.5)])),
        ];
        $languages = $this->ranker->rankAcceptLanguageHeaders($acceptLanguageHeaders);
        $this->assertEquals(['en-GB', 'en-US'], $languages);
    }

    /**
     * Tests that a language with a wildcard is ranked after languages with an equal quality score
     */
    public function testLanguageWithWildcardIsRankedAfterLanguagesWithEqualQualityScore() : void
    {
        $acceptLanguageHeaders = [
            new AcceptLanguageHeaderValue('en-US', new ImmutableHashTable([new KeyValuePair('q', 1)])),
            new AcceptLanguageHeaderValue('*', new ImmutableHashTable([new KeyValuePair('q', 1)])),
            new AcceptLanguageHeaderValue('en-GB', new ImmutableHashTable([new KeyValuePair('q', 1)])),
        ];
        $languages = $this->ranker->rankAcceptLanguageHeaders($acceptLanguageHeaders);
        $this->assertEquals(['en-US', 'en-GB', '*'], $languages);
    }

    /**
     * Tests that a language with a zero quality score is excluded
     */
    public function testLanguageWithZeroQualityScoreIsExcluded() : void
    {
        $acceptLanguageHeaders = [
            new AcceptLanguageHeaderValue('en-US', new ImmutableHashTable([new KeyValuePair('q', 1)])),
            new AcceptLanguageHeaderValue('en-GB', new ImmutableHashTable([new KeyValuePair('q', 0)])),
        ];
        $languages = $this->ranker->rankAcceptLanguageHeaders($acceptLanguageHeaders);
        $this->assertEquals(['en-US'], $languages);
    }
}
