<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http\Formatting;

use Aphiria\Net\Http\Formatting\ResponseHeaderFormatter;
use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\Headers\Cookie;
use DateTime;
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
        $this->assertEquals(
            'foo=' . urlencode('+') . '; Path=' . urlencode('/') . '; SameSite=' . urldecode('strict'),
            $this->headers->getFirst('Set-Cookie')
        );
    }

    public function testCookieWithDomainSetsDomainProperty(): void
    {
        $cookie = new Cookie('foo', 'bar', null, null, 'foo.com', false, false, null);
        $this->formatter->setCookie($this->headers, $cookie);
        $this->assertEquals(
            'foo=bar; Domain=foo.com',
            $this->headers->getFirst('Set-Cookie')
        );
    }

    public function testCookieWithExpirationSetsExpiresProperty(): void
    {
        $expiration = new DateTime();
        $cookie = new Cookie('foo', 'bar', $expiration, null, null, false, false, null);
        $this->formatter->setCookie($this->headers, $cookie);
        $this->assertEquals(
            'foo=bar; Expires=' . $expiration->format('D, d M Y H:i:s \G\M\T'),
            $this->headers->getFirst('Set-Cookie')
        );
    }

    public function testCookieWithMaxAgeSetsExpiresAndMaxAgeProperty(): void
    {
        $expiration = 3600;
        $cookie = new Cookie('foo', 'bar', time() + $expiration, null, null, false, false, null);
        $this->formatter->setCookie($this->headers, $cookie);
        $this->assertMatchesRegularExpression(
            '/^foo=bar; Expires=[^;]+; Max-Age=3600$/',
            $this->headers->getFirst('Set-Cookie')
        );
    }

    public function testCookieWithNoSameSiteDefaultsToLax(): void
    {
        $cookie = new Cookie('foo', 'bar', null, null, null, false, false);
        $this->formatter->setCookie($this->headers, $cookie);
        $this->assertEquals(
            'foo=bar; SameSite=lax',
            $this->headers->getFirst('Set-Cookie')
        );
    }

    public function testCookieWithPathSetsPathProperty(): void
    {
        $cookie = new Cookie('foo', 'bar', null, '/foo', null, false, false, null);
        $this->formatter->setCookie($this->headers, $cookie);
        $this->assertEquals(
            'foo=bar; Path=' . urlencode('/foo'),
            $this->headers->getFirst('Set-Cookie')
        );
    }

    public function testCookieWithSameSiteSetsSameSiteProperty(): void
    {
        $cookie = new Cookie('foo', 'bar', null, null, null, false, false, 'lax');
        $this->formatter->setCookie($this->headers, $cookie);
        $this->assertEquals(
            'foo=bar; SameSite=lax',
            $this->headers->getFirst('Set-Cookie')
        );
    }

    public function testDeletingCookiesAppendsToSetCookieHeader(): void
    {
        $this->formatter->deleteCookie($this->headers, 'foo');
        $this->formatter->deleteCookie($this->headers, 'bar');
        $expectedExpiration = DateTime::createFromFormat('U', '0')->format('D, d M Y H:i:s \G\M\T');
        $expectedHeaders = [
            "foo=; Expires=$expectedExpiration; Max-Age=0; HttpOnly; SameSite=lax",
            "bar=; Expires=$expectedExpiration; Max-Age=0; HttpOnly; SameSite=lax"
        ];
        $this->assertEquals($expectedHeaders, $this->headers->get('Set-Cookie'));
    }

    public function testDeletingCookieSetsExpirationAndMaxAgeToEpochAndZero(): void
    {
        $this->formatter->deleteCookie($this->headers, 'foo', null, null, false, false, null);
        $expectedExpiration = DateTime::createFromFormat('U', '0')->format('D, d M Y H:i:s \G\M\T');
        $this->assertEquals("foo=; Expires=$expectedExpiration; Max-Age=0", $this->headers->getFirst('Set-Cookie'));
    }

    public function testDeletingCookieWithSpecificDomain(): void
    {
        $this->formatter->deleteCookie($this->headers, 'foo', null, 'domain.com', false, false, null);
        $expectedExpiration = DateTime::createFromFormat('U', '0')->format('D, d M Y H:i:s \G\M\T');
        $this->assertEquals("foo=; Expires=$expectedExpiration; Max-Age=0; Domain=domain.com", $this->headers->getFirst('Set-Cookie'));
    }

    public function testDeletingCookieWithSpecificPath(): void
    {
        $this->formatter->deleteCookie($this->headers, 'foo', '/', null, false, false, null);
        $expectedExpiration = DateTime::createFromFormat('U', '0')->format('D, d M Y H:i:s \G\M\T');
        $this->assertEquals("foo=; Expires=$expectedExpiration; Max-Age=0; Path=%2F", $this->headers->getFirst('Set-Cookie'));
    }

    public function testDeletingCookieWithSecure(): void
    {
        $this->formatter->deleteCookie($this->headers, 'foo', null, null, true, false, null);
        $expectedExpiration = DateTime::createFromFormat('U', '0')->format('D, d M Y H:i:s \G\M\T');
        $this->assertEquals("foo=; Expires=$expectedExpiration; Max-Age=0; Secure", $this->headers->getFirst('Set-Cookie'));
    }

    public function testDeletingCookieWithSameSite(): void
    {
        $this->formatter->deleteCookie($this->headers, 'foo', null, null, false, false, Cookie::SAME_SITE_STRICT);
        $expectedExpiration = DateTime::createFromFormat('U', '0')->format('D, d M Y H:i:s \G\M\T');
        $this->assertEquals("foo=; Expires=$expectedExpiration; Max-Age=0; SameSite=strict", $this->headers->getFirst('Set-Cookie'));
    }

    public function testHttpOnlyCookieSetsHttpOnlyFlag(): void
    {
        $cookie = new Cookie('foo', 'bar', null, null, null, false, true, null);
        $this->formatter->setCookie($this->headers, $cookie);
        $this->assertEquals('foo=bar; HttpOnly', $this->headers->getFirst('Set-Cookie'));
    }

    public function testSecureCookieSetsSecureFlag(): void
    {
        $cookie = new Cookie('foo', 'bar', null, null, null, true, false, null);
        $this->formatter->setCookie($this->headers, $cookie);
        $this->assertEquals('foo=bar; Secure', $this->headers->getFirst('Set-Cookie'));
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
