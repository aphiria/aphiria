<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests;

use Exception;
use InvalidArgumentException;
use Opulence\Net\Uri;

/**
 * Tests the URI
 */
class UriTest extends \PHPUnit\Framework\TestCase
{
    /** @var Uri The URI to use in tests */
    private $uri;

    public function setUp(): void
    {
        $this->uri = new Uri('http://user:password@host:80/path?query#fragment');
    }

    public function testAbsolutePathUriReturnsPathAndQueryString(): void
    {
        $uri = new Uri('/foo?bar=baz');
        $this->assertEquals('/foo', $uri->getPath());
        $this->assertEquals('bar=baz', $uri->getQueryString());
    }

    public function testDoubleSlashPathWithoutAuthorityThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Uri('http:////path');
    }

    public function testFragmentReservedCharsAreEncoded(): void
    {
        $uri = new Uri('#dave=%young');
        $this->assertEquals('dave=%25young', $uri->getFragment());
    }

    public function testGettingAuthorityWithNoUserOrPasswordAndWithNonStandardPort(): void
    {
        $httpUri = new Uri('http://host:8080');
        $this->assertEquals('host:8080', $httpUri->getAuthority());
        $httpsUri = new Uri('https://host:4343');
        $this->assertEquals('host:4343', $httpsUri->getAuthority());
    }

    public function testGettingAuthorityWithNoHostOrUserInfoReturnsNull(): void
    {
        $httpUri = new Uri('');
        $this->assertNull($httpUri->getAuthority());
    }

    public function testGettingAuthorityWithUserAndPasswordIncludesUserAndPassword(): void
    {
        $uriWithUserAndPassword = new Uri('http://user:password@host');
        $this->assertEquals('user:password@host', $uriWithUserAndPassword->getAuthority());
        $this->assertEquals('host', $uriWithUserAndPassword->getAuthority(false));
        $uriWithUserButNoPassword = new Uri('http://user:@host');
        $this->assertEquals('user@host', $uriWithUserButNoPassword->getAuthority());
        $this->assertEquals('host', $uriWithUserButNoPassword->getAuthority(false));
    }

    public function testGettingFragment(): void
    {
        $this->assertEquals('fragment', $this->uri->getFragment());
    }

    public function testGettingHost(): void
    {
        $this->assertEquals('host', $this->uri->getHost());
    }

    public function testGettingPassword(): void
    {
        $this->assertEquals('password', $this->uri->getPassword());
    }

    public function testGettingPath(): void
    {
        $this->assertEquals('/path', $this->uri->getPath());
    }

    public function testGettingPort(): void
    {
        $this->assertEquals(80, $this->uri->getPort());
    }

    public function testGettingQueryString(): void
    {
        $this->assertEquals('query', $this->uri->getQueryString());
    }

    public function testGettingScheme(): void
    {
        $this->assertEquals('http', $this->uri->getScheme());
    }

    public function testGettingUser(): void
    {
        $this->assertEquals('user', $this->uri->getUser());
    }

    public function testHostIsLowerCased(): void
    {
        $uri = new Uri('http://FOO.COM');
        $this->assertEquals('foo.com', $uri->getHost());
    }

    public function testInvalidSchemeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Uri('foo://bar.com');
    }

    public function testMalformedUriThrowsExceptionWhenCreatingFromString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Uri('host:65536');
    }

    public function testOutOfRangePortThrowsException(): void
    {
        try {
            new Uri('foo.com:0');
            $this->fail('Port below acceptable range was accepted');
        } catch (InvalidArgumentException $ex) {
            // Verify we got here
            $this->assertTrue(true);
        } catch (Exception $ex) {
            // Don't want to get here
            $this->assertTrue(false);
        }

        try {
            new Uri('foo.com:65536');
            $this->fail('Port above acceptable range was accepted');
        } catch (InvalidArgumentException $ex) {
            // Verify we got here
            $this->assertTrue(true);
        } catch (Exception $ex) {
            // Don't want to get here
            $this->assertTrue(false);
        }
    }

    public function testPathReservedCharsAreEncoded(): void
    {
        $uri = new Uri('/%path');
        $this->assertEquals('/%25path', $uri->getPath());
    }

    public function testQueryStringReservedCharsAreEncoded(): void
    {
        $uri = new Uri('?dave=%young');
        $this->assertEquals('dave=%25young', $uri->getQueryString());
    }

    public function testSchemeIsLowerCased(): void
    {
        $uri = new Uri('HTTP://foo.com');
        $this->assertEquals('http', $uri->getScheme());
    }

    public function testToStringWithAllPartsIsCreatedCorrectly(): void
    {
        $uri = new Uri('http://user:password@host:8080/path?query#fragment');
        $this->assertEquals('http://user:password@host:8080/path?query#fragment', (string)$uri);
    }

    public function testToStringWithFragmentStringIncludesFragment(): void
    {
        $uri = new Uri('http://host#fragment');
        $this->assertEquals('http://host#fragment', (string)$uri);
    }

    public function testToStringWithNonStandardPortIncludesPort(): void
    {
        $httpUri = new Uri('http://host:8080');
        $this->assertEquals('http://host:8080', (string)$httpUri);
        $httpsUri = new Uri('https://host:1234');
        $this->assertEquals('https://host:1234', (string)$httpsUri);
    }

    public function testToStringWithNoSchemedDoesNotIncludeThatValue(): void
    {
        $uri = new Uri('host');
        $this->assertEquals('host', (string)$uri);
    }

    public function testToStringWithNoUserPasswordDoesNotIncludeThoseValues(): void
    {
        $uri = new Uri('http://host');
        $this->assertEquals('http://host', (string)$uri);
    }

    public function testToStringWithQueryStringIncludesQueryString(): void
    {
        $uri = new Uri('http://host?query');
        $this->assertEquals('http://host?query', (string)$uri);
    }

    public function testToStringWithUserPasswordIncludesThoseValues(): void
    {
        $uri = new Uri('http://user:password@host');
        $this->assertEquals('http://user:password@host', (string)$uri);
    }

    public function testToStringWithUserButNoPasswordOnlyIncludesUser(): void
    {
        $uri = new Uri('http://user@host');
        $this->assertEquals('http://user@host', (string)$uri);
    }
}
