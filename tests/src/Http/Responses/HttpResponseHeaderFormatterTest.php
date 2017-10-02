<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Responses;

use DateTime;
use Opulence\Net\Http\HttpHeaders;

/**
 * Tests the HTTP response header formatter
 */
class HttpResponseHeaderFormatterTest extends \PHPUnit\Framework\TestCase
{
    /** @var HttpResponseHeaderFormatter The formatter to use in tests */
    private $formatter = null;
    /** @var HttpHeaders The HTTP headers to use in tests */
    private $headers = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->formatter = new HttpResponseHeaderFormatter();
        $this->headers = new HttpHeaders();
    }

    /**
     * Tests that cookie properties with values are URL-encoded
     */
    public function testCookiePropertiesWithValuesAreUrlEncoded() : void
    {
        $cookie = new Cookie('foo', '+', null, '/', null, false, false, 'strict');
        $this->formatter->setCookie($this->headers, $cookie);
        $this->assertEquals(
            'foo=' . urlencode('+') . '; Path=' . urlencode('/') . '; SameSite=' . urldecode('strict'),
            $this->headers->get('Set-Cookie')
        );
    }

    /**
     * Tests that a cookie with a domain sets the domain property
     */
    public function testCookieWithDomainSetsDomainProperty() : void
    {
        $cookie = new Cookie('foo', 'bar', null, null, 'foo.com', false, false);
        $this->formatter->setCookie($this->headers, $cookie);
        $this->assertEquals(
            'foo=bar; Domain=foo.com',
            $this->headers->get('Set-Cookie')
        );
    }

    /**
     * Tests that a cookie with an expiration sets the expires property
     */
    public function testCookieWithExpirationSetsExpiresProperty() : void
    {
        $expiration = new DateTime();
        $cookie = new Cookie('foo', 'bar', $expiration, null, null, false, false);
        $this->formatter->setCookie($this->headers, $cookie);
        $this->assertEquals(
            'foo=bar; Expires=' . $expiration->format('D, d M Y H:i:s \G\M\T'),
            $this->headers->get('Set-Cookie')
        );
    }

    /**
     * Tests that a cookie with a max age sets the expires and max-age properties
     */
    public function testCookieWithMaxAgeSetsExpiresAndMaxAgeProperty() : void
    {
        $expiration = 3600;
        $cookie = new Cookie('foo', 'bar', $expiration, null, null, false, false);
        $this->formatter->setCookie($this->headers, $cookie);
        $this->assertEquals(
            'foo=bar; Expires=' . $cookie->getExpiration()->format('D, d M Y H:i:s \G\M\T') . '; Max-Age=3600',
            $this->headers->get('Set-Cookie')
        );
    }

    /**
     * Tests that a cookie with a path sets the path property
     */
    public function testCookieWithPathSetsPathProperty() : void
    {
        $cookie = new Cookie('foo', 'bar', null, '/foo', null, false, false);
        $this->formatter->setCookie($this->headers, $cookie);
        $this->assertEquals(
            'foo=bar; Path=' . urlencode('/foo'),
            $this->headers->get('Set-Cookie')
        );
    }

    /**
     * Tests that a cookie with a same-site sets the same-site property
     */
    public function testCookieWithSameSiteSetsSameSiteProperty() : void
    {
        $cookie = new Cookie('foo', 'bar', null, null, null, false, false, 'lax');
        $this->formatter->setCookie($this->headers, $cookie);
        $this->assertEquals(
            'foo=bar; SameSite=lax',
            $this->headers->get('Set-Cookie')
        );
    }

    /**
     * Tests that deleting a cookie sets the expiration to the epoch and the max-age to zero
     */
    public function testDeletingCookieSetsExpirationAndMaxAgeToEpochAndZero() : void
    {
        $this->formatter->deleteCookie($this->headers, 'foo', null, null, false, false);
        $expectedExpiration = DateTime::createFromFormat('U', 0)->format('D, d M Y H:i:s \G\M\T');
        $this->assertEquals("foo=; Expires=$expectedExpiration; Max-Age=0", $this->headers->get('Set-Cookie'));
    }

    /**
     * Tests that an HTTP-only cookie sets the HTTP-only flag
     */
    public function testHttpOnlyCookieSetsHttpOnlyFlag() : void
    {
        $cookie = new Cookie('foo', 'bar', null, null, null, false, true);
        $this->formatter->setCookie($this->headers, $cookie);
        $this->assertEquals('foo=bar; HttpOnly', $this->headers->get('Set-Cookie'));
    }

    /**
     * Tests that a secure cookie sets the secure flag
     */
    public function testSecureCookieSetsSecureFlag() : void
    {
        $cookie = new Cookie('foo', 'bar', null, null, null, true, false);
        $this->formatter->setCookie($this->headers, $cookie);
        $this->assertEquals('foo=bar; Secure', $this->headers->get('Set-Cookie'));
    }

    /**
     * Tests that setting cookies appends to the cookie header
     */
    public function testSettingCookieAppendsToCookieHeader() : void
    {
        $cookie1 = new Cookie('foo', 'bar', null, null, null, false, false);
        $cookie2 = new Cookie('baz', 'blah', null, null, null, false, false);
        $this->formatter->setCookie($this->headers, $cookie1);
        $this->formatter->setCookie($this->headers, $cookie2);
        $expectedHeader = ['foo=bar', 'baz=blah'];
        $this->assertEquals($expectedHeader, $this->headers->get('Set-Cookie', null, false));
    }

    /**
     * Tests that setting multiple cookies appends to the cookie header
     */
    public function testSettingMultipleCookiesAppendsToCookieHeader() : void
    {
        $cookie1 = new Cookie('foo', 'bar', null, null, null, false, false);
        $cookie2 = new Cookie('baz', 'blah', null, null, null, false, false);
        $this->formatter->setCookies($this->headers, [$cookie1, $cookie2]);
        $expectedHeader = ['foo=bar', 'baz=blah'];
        $this->assertEquals($expectedHeader, $this->headers->get('Set-Cookie', null, false));
    }
}
