<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http\Formatting;

use Aphiria\Net\Http\ContentNegotiation\AcceptLanguageMatcher;
use Aphiria\Net\Http\HttpHeaders;
use PHPUnit\Framework\TestCase;

/**
 * Tests the accept language matcher
 */
class AcceptLanguageMatcherTest extends TestCase
{
    public function testLanguageIsNullWhenNoMatchesAreFound(): void
    {
        $headers = new HttpHeaders();
        $headers->add('Accept-Language', 'en-US; q=1');
        $this->assertNull((new AcceptLanguageMatcher([]))->getBestLanguageMatch($headers));
        $this->assertNull((new AcceptLanguageMatcher(['en-GB']))->getBestLanguageMatch($headers));
    }

    public function testLanguagesAreRankedInOrderOfQualityScore(): void
    {
        $headers = new HttpHeaders();
        $headers->add('Accept-Language', 'en-US; q=0.1');
        $headers->add('Accept-Language', 'en-GB; q=0.5', true);
        $this->assertEquals('en-GB', (new AcceptLanguageMatcher(['en-US', 'en-GB']))->getBestLanguageMatch($headers));
    }

    public function testLanguageWithWildcardIsRankedAfterLanguagesWithEqualQualityScore(): void
    {
        $headers = new HttpHeaders();
        $headers->add('Accept-Language', 'en-US; q=1');
        $headers->add('Accept-Language', '*; q=1', true);
        $headers->add('Accept-Language', 'en-GB; q=1', true);
        $this->assertEquals('en-US', (new AcceptLanguageMatcher(['en-GB', 'en-US']))->getBestLanguageMatch($headers));
    }

    public function testLanguageWithZeroQualityScoreIsExcluded(): void
    {
        $headers = new HttpHeaders();
        $headers->add('Accept-Language', 'en-US; q=1');
        $headers->add('Accept-Language', 'en-GB; q=0', true);
        $this->assertNull((new AcceptLanguageMatcher(['en-GB']))->getBestLanguageMatch($headers));
    }

    public function testTruncatedLanguageCanMatchSupportedLanguage(): void
    {
        $headers = new HttpHeaders();
        $headers->add('Accept-Language', 'en-US-123; q=1');
        $this->assertEquals('en-US', (new AcceptLanguageMatcher(['en-US']))->getBestLanguageMatch($headers));

        $headers = new HttpHeaders();
        $headers->add('Accept-Language', 'en-US; q=1');
        $this->assertEquals('en', (new AcceptLanguageMatcher(['en']))->getBestLanguageMatch($headers));
    }

    public function testWildcardWithHighestScoreMatchesFirstSupportedLanguage(): void
    {
        $headers = new HttpHeaders();
        $headers->add('Accept-Language', '*; q=1');
        $headers->add('Accept-Language', 'en-GB; q=0.5', true);
        $this->assertEquals('de-DE', (new AcceptLanguageMatcher(['de-DE']))->getBestLanguageMatch($headers));
    }
}
