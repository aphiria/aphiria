<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
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
    private $uri = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->uri = new Uri('http://user:password@host:80/path?query#fragment');
    }

    /**
     * Tests that an absolute path URI returns the path and query string
     */
    public function testAbsolutePathUriReturnsPathAndQueryString() : void
    {
        $uri = new Uri('/foo?bar=baz');
        $this->assertEquals('/foo', $uri->getPath());
        $this->assertEquals('bar=baz', $uri->getQueryString());
    }

    /**
     * Tests that a double-slash path without an authority throws an exception
     */
    public function testDoubleSlashPathWithoutAuthorityThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new Uri('http:////path');
    }

    /**
     * Tests that reserve characters in the fragment are encoded
     */
    public function testFragmentReservedCharsAreEncoded() : void
    {
        $uri = new Uri('#dave=%young');
        $this->assertEquals('dave=%25young', $uri->getFragment());
    }

    /**
     * Tests getting the authority with no user or password and with a non-standard port
     */
    public function testGettingAuthorityWithNoUserOrPasswordAndWithNonStandardPort() : void
    {
        $httpUri = new Uri('http://host:8080');
        $this->assertEquals('host:8080', $httpUri->getAuthority());
        $httpsUri = new Uri('https://host:4343');
        $this->assertEquals('host:4343', $httpsUri->getAuthority());
    }

    /**
     * Tests getting the authority with no user, password, and host returns null
     */
    public function testGettingAuthorityWithNoHostOrUserInfoReturnsNull() : void
    {
        $httpUri = new Uri('');
        $this->assertNull($httpUri->getAuthority());
    }

    /**
     * Tests getting the authority with user and password includes the user and password
     */
    public function testGettingAuthorityWithUserAndPasswordIncludesUserAndPassword() : void
    {
        $uriWithUserAndPassword = new Uri('http://user:password@host');
        $this->assertEquals('user:password@host', $uriWithUserAndPassword->getAuthority());
        $this->assertEquals('host', $uriWithUserAndPassword->getAuthority(false));
        $uriWithUserButNoPassword = new Uri('http://user:@host');
        $this->assertEquals('user@host', $uriWithUserButNoPassword->getAuthority());
        $this->assertEquals('host', $uriWithUserButNoPassword->getAuthority(false));
    }

    /**
     * Tests getting the fragment
     */
    public function testGettingFragment() : void
    {
        $this->assertEquals('fragment', $this->uri->getFragment());
    }

    /**
     * Tests getting the host
     */
    public function testGettingHost() : void
    {
        $this->assertEquals('host', $this->uri->getHost());
    }

    /**
     * Tests getting the password
     */
    public function testGettingPassword() : void
    {
        $this->assertEquals('password', $this->uri->getPassword());
    }

    /**
     * Tests getting the path
     */
    public function testGettingPath() : void
    {
        $this->assertEquals('/path', $this->uri->getPath());
    }

    /**
     * Tests getting the port
     */
    public function testGettingPort() : void
    {
        $this->assertEquals(80, $this->uri->getPort());
    }

    /**
     * Tests getting the query string
     */
    public function testGettingQueryString() : void
    {
        $this->assertEquals('query', $this->uri->getQueryString());
    }

    /**
     * Tests getting the scheme
     */
    public function testGettingScheme() : void
    {
        $this->assertEquals('http', $this->uri->getScheme());
    }

    /**
     * Tests getting the user
     */
    public function testGettingUser() : void
    {
        $this->assertEquals('user', $this->uri->getUser());
    }

    /**
     * Tests that the host is lower-cased
     */
    public function testHostIsLowerCased() : void
    {
        $uri = new Uri('http://FOO.COM');
        $this->assertEquals('foo.com', $uri->getHost());
    }

    /**
     * Tests that an invalid scheme throws an exception
     */
    public function testInvalidSchemeThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new Uri('foo://bar.com');
    }

    /**
     * Tests a malformed URI throws an exception when creating from string
     */
    public function testMalformedUriThrowsExceptionWhenCreatingFromString() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new Uri('host:65536');
    }

    /**
     * Tests that an out of range port throws an exception
     */
    public function testOutOfRangePortThrowsException() : void
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

    /**
     * Tests that reserve characeters in the path are encoded
     */
    public function testPathReservedCharsAreEncoded() : void
    {
        $uri = new Uri('/%path');
        $this->assertEquals('/%25path', $uri->getPath());
    }

    /**
     * Tests that reserve characeters in the query string are encoded
     */
    public function testQueryStringReservedCharsAreEncoded() : void
    {
        $uri = new Uri('?dave=%young');
        $this->assertEquals('dave=%25young', $uri->getQueryString());
    }

    /**
     * Tests that the scheme is lower-cased
     */
    public function testSchemeIsLowerCased() : void
    {
        $uri = new Uri('HTTP://foo.com');
        $this->assertEquals('http', $uri->getScheme());
    }

    /**
     * Tests casting to string with all parts is created correctly
     */
    public function testToStringWithAllPartsIsCreatedCorrectly() : void
    {
        $uri = new Uri('http://user:password@host:8080/path?query#fragment');
        $this->assertEquals('http://user:password@host:8080/path?query#fragment', (string)$uri);
    }

    /**
     * Tests casting to string with fragment includes the fragment
     */
    public function testToStringWithFragmentStringIncludesFragment() : void
    {
        $uri = new Uri('http://host#fragment');
        $this->assertEquals('http://host#fragment', (string)$uri);
    }

    /**
     * Tests casting to string with a non-standard port includes the port
     */
    public function testToStringWithNonStandardPortIncludesPort() : void
    {
        $httpUri = new Uri('http://host:8080');
        $this->assertEquals('http://host:8080', (string)$httpUri);
        $httpsUri = new Uri('https://host:1234');
        $this->assertEquals('https://host:1234', (string)$httpsUri);
    }

    /**
     * Tests casting to string with no scheme does not include that value
     */
    public function testToStringWithNoSchemedDoesNotIncludeThatValue() : void
    {
        $uri = new Uri('host');
        $this->assertEquals('host', (string)$uri);
    }

    /**
     * Tests casting to string with no user or password does not include those value
     */
    public function testToStringWithNoUserPasswordDoesNotIncludeThoseValues() : void
    {
        $uri = new Uri('http://host');
        $this->assertEquals('http://host', (string)$uri);
    }

    /**
     * Tests casting to string with query string includes the query string
     */
    public function testToStringWithQueryStringIncludesQueryString() : void
    {
        $uri = new Uri('http://host?query');
        $this->assertEquals('http://host?query', (string)$uri);
    }

    /**
     * Tests casting to string with user or password incldues those value
     */
    public function testToStringWithUserPasswordIncludesThoseValues() : void
    {
        $uri = new Uri('http://user:password@host');
        $this->assertEquals('http://user:password@host', (string)$uri);
    }

    /**
     * Tests casting to string with user but no password only includes the user
     */
    public function testToStringWithUserButNoPasswordOnlyIncludesUser() : void
    {
        $uri = new Uri('http://user@host');
        $this->assertEquals('http://user@host', (string)$uri);
    }
}
