<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ContentNegotiation\Tests;

use Aphiria\ContentNegotiation\AcceptLanguageMatcher;
use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\Request;
use Aphiria\Net\Uri;
use PHPUnit\Framework\TestCase;

class AcceptLanguageMatcherTest extends TestCase
{
    private Headers $headers;
    private IRequest $request;

    protected function setUp(): void
    {
        $this->headers = new Headers();
        $this->request = new Request('GET', new Uri('http://example.com'), $this->headers);
    }

    public function testLanguageIsFirstSupportedWhenLanguagesAreEqualScoreWildcards(): void
    {
        $this->headers->add('Accept-Language', '*');
        $this->headers->add('Accept-Language', '*', true);
        $this->assertSame('en-US', (new AcceptLanguageMatcher(['en-US', 'en-GB']))->getBestLanguageMatch($this->request));
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
        $this->assertSame('en-GB', (new AcceptLanguageMatcher(['en-US', 'en-GB']))->getBestLanguageMatch($this->request));
    }

    public function testLanguageWithWildcardIsRankedAfterLanguagesWithEqualQualityScore(): void
    {
        $this->headers->add('Accept-Language', 'en-US; q=1');
        $this->headers->add('Accept-Language', '*; q=1', true);
        $this->headers->add('Accept-Language', 'en-GB; q=1', true);
        $this->assertSame('en-US', (new AcceptLanguageMatcher(['en-GB', 'en-US']))->getBestLanguageMatch($this->request));
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
        $this->assertSame('en-US', (new AcceptLanguageMatcher(['en-US']))->getBestLanguageMatch($this->request));

        // Purposely overwriting the previous Accept-Language header
        $this->headers->add('Accept-Language', 'en-US; q=1');
        $this->assertSame('en', (new AcceptLanguageMatcher(['en']))->getBestLanguageMatch($this->request));
    }

    public function testWildcardWithHighestScoreMatchesFirstSupportedLanguage(): void
    {
        $this->headers->add('Accept-Language', '*; q=1');
        $this->headers->add('Accept-Language', 'en-GB; q=0.5', true);
        $this->assertSame('de-DE', (new AcceptLanguageMatcher(['de-DE']))->getBestLanguageMatch($this->request));
    }
}
