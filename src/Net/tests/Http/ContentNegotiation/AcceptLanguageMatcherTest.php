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
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\Request;
use Aphiria\Net\Uri;
use PHPUnit\Framework\TestCase;

/**
 * Tests the accept language matcher
 */
class AcceptLanguageMatcherTest extends TestCase
{
    private HttpHeaders $headers;
    private IHttpRequestMessage $request;

    protected function setUp(): void
    {
        $this->headers = new HttpHeaders();
        $this->request = new Request('GET', new Uri('http://example.com'), $this->headers);
    }

    public function testLanguageIsNullWhenNoMatchesAreFound(): void
    {
        $this->headers->add('Accept-Language', 'en-US; q=1');
        $this->assertNull((new AcceptLanguageMatcher([]))->getBestLanguageMatch($this->request));
        $this->assertNull((new AcceptLanguageMatcher(['en-GB']))->getBestLanguageMatch($this->request));
    }

    public function testLanguagesAreRankedInOrderOfQualityScore(): void
    {
        $this->headers->add('Accept-Language', 'en-US; q=0.1');
        $this->headers->add('Accept-Language', 'en-GB; q=0.5', true);
        $this->assertEquals('en-GB', (new AcceptLanguageMatcher(['en-US', 'en-GB']))->getBestLanguageMatch($this->request));
    }

    public function testLanguageWithWildcardIsRankedAfterLanguagesWithEqualQualityScore(): void
    {
        $this->headers->add('Accept-Language', 'en-US; q=1');
        $this->headers->add('Accept-Language', '*; q=1', true);
        $this->headers->add('Accept-Language', 'en-GB; q=1', true);
        $this->assertEquals('en-US', (new AcceptLanguageMatcher(['en-GB', 'en-US']))->getBestLanguageMatch($this->request));
    }

    public function testLanguageWithZeroQualityScoreIsExcluded(): void
    {
        $this->headers->add('Accept-Language', 'en-US; q=1');
        $this->headers->add('Accept-Language', 'en-GB; q=0', true);
        $this->assertNull((new AcceptLanguageMatcher(['en-GB']))->getBestLanguageMatch($this->request));
    }

    public function testTruncatedLanguageCanMatchSupportedLanguage(): void
    {
        $this->headers->add('Accept-Language', 'en-US-123; q=1');
        $this->assertEquals('en-US', (new AcceptLanguageMatcher(['en-US']))->getBestLanguageMatch($this->request));

        // Purposely overwriting the previous Accept-Language header
        $this->headers->add('Accept-Language', 'en-US; q=1');
        $this->assertEquals('en', (new AcceptLanguageMatcher(['en']))->getBestLanguageMatch($this->request));
    }

    public function testWildcardWithHighestScoreMatchesFirstSupportedLanguage(): void
    {
        $this->headers->add('Accept-Language', '*; q=1');
        $this->headers->add('Accept-Language', 'en-GB; q=0.5', true);
        $this->assertEquals('de-DE', (new AcceptLanguageMatcher(['de-DE']))->getBestLanguageMatch($this->request));
    }
}
