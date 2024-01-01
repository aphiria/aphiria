<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests;

use Aphiria\Net\Uri;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class UriTest extends TestCase
{
    private Uri $uri;

    protected function setUp(): void
    {
        $this->uri = new Uri('http://user:password@host:80/path?query#fragment');
    }

    public function testAbsolutePathUriReturnsPathAndQueryString(): void
    {
        $uri = new Uri('/foo?bar=baz');
        $this->assertSame('/foo', $uri->path);
        $this->assertSame('bar=baz', $uri->queryString);
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
        $this->assertSame('dave=%25young', $uri->fragment);
    }

    public function testGettingAuthorityWithNoHostOrUserInfoReturnsNull(): void
    {
        $httpUri = new Uri('');
        $this->assertNull($httpUri->getAuthority());
    }

    /**
     * @param string $uri The URI
     * @param string $expectedUri The expected URI
     */
    #[TestWith(['http://host:8080', 'host:8080'])]
    #[TestWith(['https://host:4343', 'host:4343'])]
    public function testGettingAuthorityWithNoUserOrPasswordAndWithNonStandardPort(string $uri, string $expectedUri): void
    {
        $httpUri = new Uri($uri);
        $this->assertEquals($expectedUri, $httpUri->getAuthority());
    }

    /**
     * @param string $uri The URI
     * @param string $expectedUri The expected URI
     */
    #[TestWith(['http://user:password@host', 'user:password@host'])]
    #[TestWith(['http://user:@host', 'user@host'])]
    public function testGettingAuthorityWithUserAndPasswordIncludesUserAndPassword(string $uri, string $expectedUri): void
    {
        $uriWithUserAndPassword = new Uri($uri);
        $this->assertEquals($expectedUri, $uriWithUserAndPassword->getAuthority());
        $this->assertSame('host', $uriWithUserAndPassword->getAuthority(false));
    }

    public function testGettingFragment(): void
    {
        $this->assertSame('fragment', $this->uri->fragment);
    }

    public function testGettingHost(): void
    {
        $this->assertSame('host', $this->uri->host);
    }

    public function testGettingPassword(): void
    {
        $this->assertSame('password', $this->uri->password);
    }

    public function testGettingPath(): void
    {
        $this->assertSame('/path', $this->uri->path);
    }

    public function testGettingPort(): void
    {
        $this->assertSame(80, $this->uri->port);
    }

    public function testGettingQueryString(): void
    {
        $this->assertSame('query', $this->uri->queryString);
    }

    public function testGettingScheme(): void
    {
        $this->assertSame('http', $this->uri->scheme);
    }

    public function testGettingUser(): void
    {
        $this->assertSame('user', $this->uri->user);
    }

    public function testHostIsLowerCased(): void
    {
        $uri = new Uri('http://FOO.COM');
        $this->assertSame('foo.com', $uri->host);
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

    public function testOutOfRangePortThrowsException(): void
    {
        $invalidUri = 'foo.com:65536';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("{$invalidUri} is malformed");
        new Uri($invalidUri);
    }

    public function testPathReservedCharsAreEncoded(): void
    {
        $uri = new Uri('/%path');
        $this->assertSame('/%25path', $uri->path);
    }

    public function testQueryStringReservedCharsAreEncoded(): void
    {
        $uri = new Uri('?dave=%young');
        $this->assertSame('dave=%25young', $uri->queryString);
    }

    public function testSchemeIsLowerCased(): void
    {
        $uri = new Uri('HTTP://foo.com');
        $this->assertSame('http', $uri->scheme);
    }

    public function testToStringWithAllPartsIsCreatedCorrectly(): void
    {
        $uri = new Uri('http://user:password@host:8080/path?query#fragment');
        $this->assertSame('http://user:password@host:8080/path?query#fragment', (string)$uri);
    }

    public function testToStringWithFragmentStringIncludesFragment(): void
    {
        $uri = new Uri('http://host#fragment');
        $this->assertSame('http://host#fragment', (string)$uri);
    }

    /**
     * @param string $uri The URI
     */
    #[TestWith(['http://host:8080'])]
    #[TestWith(['https://host:1234'])]
    public function testToStringWithNonStandardPortIncludesPort(string $uri): void
    {
        $httpUri = new Uri($uri);
        $this->assertEquals($uri, (string)$httpUri);
    }

    public function testToStringWithNoSchemedDoesNotIncludeThatValue(): void
    {
        $uri = new Uri('host');
        $this->assertSame('host', (string)$uri);
    }

    public function testToStringWithNoUserPasswordDoesNotIncludeThoseValues(): void
    {
        $uri = new Uri('http://host');
        $this->assertSame('http://host', (string)$uri);
    }

    public function testToStringWithQueryStringIncludesQueryString(): void
    {
        $uri = new Uri('http://host?query');
        $this->assertSame('http://host?query', (string)$uri);
    }

    public function testToStringWithUserButNoPasswordOnlyIncludesUser(): void
    {
        $uri = new Uri('http://user@host');
        $this->assertSame('http://user@host', (string)$uri);
    }

    public function testToStringWithUserPasswordIncludesThoseValues(): void
    {
        $uri = new Uri('http://user:password@host');
        $this->assertSame('http://user:password@host', (string)$uri);
    }
}
