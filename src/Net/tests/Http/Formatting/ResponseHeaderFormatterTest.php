<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http\Formatting;

use Aphiria\Net\Http\Formatting\ResponseHeaderFormatter;
use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\Headers\Cookie;
use PHPUnit\Framework\TestCase;

class ResponseHeaderFormatterTest extends TestCase
{
    private ResponseHeaderFormatter $formatter;
    private Headers $headers;

    protected function setUp(): void
    {
        $this->formatter = new ResponseHeaderFormatter();
        $this->headers = new Headers();
    }

    public function testCookiePropertiesWithValuesAreUrlEncoded(): void
    {
        $cookie = new Cookie('foo', '+', null, '/', null, false, false, 'strict');
        $this->formatter->setCookie($this->headers, $cookie);
        $this->assertSame(
            'foo=' . urlencode('+') . '; Path=' . urlencode('/') . '; SameSite=' . urldecode('strict'),
            $this->headers->getFirst('Set-Cookie')
        );
    }

    public function testCookieWithDomainSetsDomainProperty(): void
    {
        $cookie = new Cookie('foo', 'bar', null, null, 'foo.com', false, false, null);
        $this->formatter->setCookie($this->headers, $cookie);
        $this->assertSame(
            'foo=bar; Domain=foo.com',
            $this->headers->getFirst('Set-Cookie')
        );
    }

    public function testCookieWithMaxAgeSetsMaxAge(): void
    {
        $maxAge = 3600;
        $cookie = new Cookie('foo', 'bar', $maxAge, null, null, false, false, null);
        $this->formatter->setCookie($this->headers, $cookie);
        $this->assertSame(
            'foo=bar; Max-Age=3600',
            $this->headers->getFirst('Set-Cookie')
        );
    }

    public function testCookieWithNoSameSiteDefaultsToLax(): void
    {
        $cookie = new Cookie('foo', 'bar', null, null, null, false, false);
        $this->formatter->setCookie($this->headers, $cookie);
        $this->assertSame(
            'foo=bar; SameSite=lax',
            $this->headers->getFirst('Set-Cookie')
        );
    }

    public function testCookieWithPathSetsPathProperty(): void
    {
        $cookie = new Cookie('foo', 'bar', null, '/foo', null, false, false, null);
        $this->formatter->setCookie($this->headers, $cookie);
        $this->assertSame(
            'foo=bar; Path=' . urlencode('/foo'),
            $this->headers->getFirst('Set-Cookie')
        );
    }

    public function testCookieWithSameSiteSetsSameSiteProperty(): void
    {
        $cookie = new Cookie('foo', 'bar', null, null, null, false, false, 'lax');
        $this->formatter->setCookie($this->headers, $cookie);
        $this->assertSame(
            'foo=bar; SameSite=lax',
            $this->headers->getFirst('Set-Cookie')
        );
    }

    public function testDeletingCookiesAppendsToSetCookieHeader(): void
    {
        $this->formatter->deleteCookie($this->headers, 'foo');
        $this->formatter->deleteCookie($this->headers, 'bar');
        $expectedHeaders = [
            'foo=; Max-Age=0; HttpOnly; SameSite=lax',
            'bar=; Max-Age=0; HttpOnly; SameSite=lax'
        ];
        $this->assertEquals($expectedHeaders, $this->headers->get('Set-Cookie'));
    }

    public function testDeletingCookieWithSpecificDomain(): void
    {
        $this->formatter->deleteCookie($this->headers, 'foo', null, 'domain.com', false, false, null);
        $this->assertSame('foo=; Max-Age=0; Domain=domain.com', $this->headers->getFirst('Set-Cookie'));
    }

    public function testDeletingCookieWithSpecificPath(): void
    {
        $this->formatter->deleteCookie($this->headers, 'foo', '/', null, false, false, null);
        $this->assertSame('foo=; Max-Age=0; Path=%2F', $this->headers->getFirst('Set-Cookie'));
    }

    public function testDeletingCookieWithSecure(): void
    {
        $this->formatter->deleteCookie($this->headers, 'foo', null, null, true, false, null);
        $this->assertSame('foo=; Max-Age=0; Secure', $this->headers->getFirst('Set-Cookie'));
    }

    public function testDeletingCookieWithSameSite(): void
    {
        $this->formatter->deleteCookie($this->headers, 'foo', null, null, false, false, Cookie::SAME_SITE_STRICT);
        $this->assertSame('foo=; Max-Age=0; SameSite=strict', $this->headers->getFirst('Set-Cookie'));
    }

    public function testHttpOnlyCookieSetsHttpOnlyFlag(): void
    {
        $cookie = new Cookie('foo', 'bar', null, null, null, false, true, null);
        $this->formatter->setCookie($this->headers, $cookie);
        $this->assertSame('foo=bar; HttpOnly', $this->headers->getFirst('Set-Cookie'));
    }

    public function testSecureCookieSetsSecureFlag(): void
    {
        $cookie = new Cookie('foo', 'bar', null, null, null, true, false, null);
        $this->formatter->setCookie($this->headers, $cookie);
        $this->assertSame('foo=bar; Secure', $this->headers->getFirst('Set-Cookie'));
    }

    public function testSettingCookieAppendsToCookieHeader(): void
    {
        $cookie1 = new Cookie('foo', 'bar', null, null, null, false, false, null);
        $cookie2 = new Cookie('baz', 'blah', null, null, null, false, false, null);
        $this->formatter->setCookie($this->headers, $cookie1);
        $this->formatter->setCookie($this->headers, $cookie2);
        $expectedHeader = ['foo=bar', 'baz=blah'];
        $this->assertEquals($expectedHeader, $this->headers->get('Set-Cookie'));
    }

    public function testSettingMultipleCookiesAppendsToCookieHeader(): void
    {
        $cookie1 = new Cookie('foo', 'bar', null, null, null, false, false, null);
        $cookie2 = new Cookie('baz', 'blah', null, null, null, false, false, null);
        $this->formatter->setCookies($this->headers, [$cookie1, $cookie2]);
        $expectedHeader = ['foo=bar', 'baz=blah'];
        $this->assertEquals($expectedHeader, $this->headers->get('Set-Cookie'));
    }
}
