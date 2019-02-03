<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/aphiria/net/blob/master/LICENSE.md
 */

namespace Aphiria\Net\Tests;

use Exception;
use InvalidArgumentException;
use Aphiria\Net\Uri;
use PHPUnit\Framework\TestCase;

/**
 * Tests the URI
 */
class UriTest extends TestCase
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
        $this->expectExceptionMessage('URI http:////path is malformed');
        new Uri('http:////path');
    }

    public function testFragmentReservedCharsAreEncoded(): void
    {
        $uri = new Uri('#dave=%young');
        $this->assertEquals('dave=%25young', $uri->getFragment());
    }

    public function authorityWithNoUserPasswordProvider(): array
    {
        return [
            ['http://host:8080', 'host:8080'],
            ['https://host:4343', 'host:4343'],
        ];
    }

    /**
     * @dataProvider authorityWithNoUserPasswordProvider
     */
    public function testGettingAuthorityWithNoUserOrPasswordAndWithNonStandardPort($uri, $expectedUri): void
    {
        $httpUri = new Uri($uri);
        $this->assertEquals($expectedUri, $httpUri->getAuthority());
    }

    public function testGettingAuthorityWithNoHostOrUserInfoReturnsNull(): void
    {
        $httpUri = new Uri('');
        $this->assertNull($httpUri->getAuthority());
    }

    public function authorityWithUserPasswordProvider(): array
    {
        return [
            ['http://user:password@host', 'user:password@host'],
            ['http://user:@host', 'user@host'],
        ];
    }

    /**
     * @dataProvider authorityWithUserPasswordProvider
     */
    public function testGettingAuthorityWithUserAndPasswordIncludesUserAndPassword($uri, $expectedUri): void
    {
        $uriWithUserAndPassword = new Uri($uri);
        $this->assertEquals($expectedUri, $uriWithUserAndPassword->getAuthority());
        $this->assertEquals('host', $uriWithUserAndPassword->getAuthority(false));
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
        $this->expectExceptionMessage('Scheme "foo" is invalid');
        new Uri('foo://bar.com');
    }

    public function testMalformedUriThrowsExceptionWhenCreatingFromString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('URI host:65536 is malformed');
        new Uri('host:65536');
    }

    public function outOfRangePortProvider(): array
    {
        return [
            ['foo.com:0'],
            ['foo.com:65536'],
        ];
    }

    /**
     * @dataProvider outOfRangePortProvider
     */
    public function testOutOfRangePortThrowsException($invalidUri): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("{$invalidUri} is malformed");
        new Uri($invalidUri);
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

    public function httpUriProvider(): array
    {
        return [
            ['http://host:8080'],
            ['https://host:1234'],
        ];
    }

    /**
     * @dataProvider httpUriProvider
     */
    public function testToStringWithNonStandardPortIncludesPort($uri): void
    {
        $httpUri = new Uri($uri);
        $this->assertEquals($uri, (string)$httpUri);
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
