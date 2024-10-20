<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http;

use Aphiria\Net\Http\RequestFactory;
use Aphiria\Net\Http\StreamBody;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class RequestFactoryTest extends TestCase
{
    private RequestFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new RequestFactory();
    }

    public static function httpServerPropertyProvider(): array
    {
        return [
            [
                [
                    'SERVER_PROTOCOL' => 'HTTP/1.1',
                    'HTTPS' => 'on',
                    'HTTP_HOST' => 'foo.com',
                ],
                'https',
            ],
            [
                [
                    'SERVER_PROTOCOL' => 'HTTP/1.1',
                    'HTTPS' => 'off',
                    'HTTP_HOST' => 'foo.com',
                ],
                'http',
            ],
            [
                [
                    'SERVER_PROTOCOL' => 'HTTP/1.1',
                    'HTTP_HOST' => 'foo.com',
                ],
                'http',
            ],
        ];
    }

    public function testAuthorityInServerSetsAuthorityInUri(): void
    {
        $request = $this->factory->createRequestFromSuperglobals([
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW' => 'pw',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertSame('user', $request->uri->user);
        $this->assertSame('pw', $request->uri->password);
    }

    public function testBodyIsCreatedFromInputStream(): void
    {
        $request = $this->factory->createRequestFromSuperglobals(['HTTP_HOST' => 'foo.com']);
        $this->assertInstanceOf(StreamBody::class, $request->body);
    }

    public function testCertainHeaderValuesAreUrlDecoded(): void
    {
        // Only cookies should be decoded
        $request = $this->factory->createRequestFromSuperglobals([
            'HTTP_FOO' => '%25',
            'HTTP_COOKIE' => '%25',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertSame('%25', $request->headers->getFirst('Foo'));
        $this->assertSame('%', $request->headers->getFirst('Cookie'));
    }

    public function testClientIPAddressIsSetAsPropertyWhenUsingTrustedProxy(): void
    {
        $factory = new RequestFactory(['192.168.1.1']);
        $request = $factory->createRequestFromSuperglobals([
            'REMOTE_ADDR' => '192.168.1.1',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertSame('192.168.1.1', $request->properties->get('CLIENT_IP_ADDRESS'));
    }

    /**
     * @param string $ipDatum1 The IP address
     * @param string $expectedIpDatum The expected IP address
     */
    #[TestWith(['for="_gazonk"', '_gazonk'])]
    #[TestWith(['for="[2001:db8:cafe::17]:4711"', '2001:db8:cafe::17'])]
    #[TestWith(['for=192.0.2.60;proto=http;by=203.0.113.43', '192.0.2.60'])]
    #[TestWith(['for=192.0.2.43, for=198.51.100.17', '198.51.100.17'])]
    public function testClientIPAddressIsSetFromForwardedHeaderWhenUsingTrustedProxy(string $ipDatum1, string $expectedIpDatum): void
    {
        $_SERVER['HTTP_FORWARDED'] = $ipDatum1;
        $factory = new RequestFactory([], ['HTTP_FORWARDED' => 'HTTP_FORWARDED']);
        $request = $factory->createRequestFromSuperglobals([
            'HTTP_FORWARDED' => $ipDatum1,
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertEquals($expectedIpDatum, $request->properties->get('CLIENT_IP_ADDRESS'));
    }

    public function testClientIPHeaderUsedWhenSet(): void
    {
        $factory = new RequestFactory([], ['HTTP_CLIENT_IP' => 'HTTP_CLIENT_IP', 'HTTP_FORWARDED' => 'FORWARDED']);
        $request = $factory->createRequestFromSuperglobals([
            'HTTP_CLIENT_IP' => '192.168.1.1',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertSame('192.168.1.1', $request->properties->get('CLIENT_IP_ADDRESS'));
    }

    public function testClientPortUsedToDeterminePortWithTrustedProxy(): void
    {
        $factory = new RequestFactory(['192.168.1.1'], ['HTTP_CLIENT_PORT' => 'HTTP_X_FORWARDED_PORT']);
        $request = $factory->createRequestFromSuperglobals([
            'REMOTE_ADDR' => '192.168.1.1',
            'HTTP_X_FORWARDED_PORT' => 8080,
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertSame(8080, $request->uri->port);
    }

    public function testClientProtoUsedToDeterminePortWithTrustedProxy(): void
    {
        $factory = new RequestFactory(['192.168.1.1'], ['HTTP_CLIENT_PROTO' => 'HTTP_X_FORWARDED_PROTO']);
        $request = $factory->createRequestFromSuperglobals([
            'REMOTE_ADDR' => '192.168.1.1',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertSame(443, $request->uri->port);
    }

    /**
     * @param string $remoteAddress The remote address
     * @param string $forwardedProto The forwarded proto
     * @param string $host the host
     * @param string $expectedScheme The expected scheme
     * @param string $message The error message in case the assertion fails
     */
    #[TestWith(['192.168.1.1', 'HTTPS', 'foo.com', 'https', 'Try with HTTPS'])]
    #[TestWith(['192.168.1.1', 'ssl', 'foo.com', 'https', 'Try with SSL'])]
    #[TestWith(['192.168.1.1', 'on', 'foo.com', 'https', 'Try with "on"'])]
    #[TestWith(['192.168.1.1', 'HTTP', 'foo.com', 'http', 'Try with HTTP'])]
    public function testClientProtoUsedToSetSchemeWithTrustedProxy(string $remoteAddress, string $forwardedProto, string $host, string $expectedScheme, string $message): void
    {
        $factory = new RequestFactory(['192.168.1.1'], ['HTTP_CLIENT_PROTO' => 'HTTP_X_FORWARDED_PROTO']);
        $request = $factory->createRequestFromSuperglobals([
            'REMOTE_ADDR' => $remoteAddress,
            'HTTP_X_FORWARDED_PROTO' => $forwardedProto,
            'HTTP_HOST' => $host,
        ]);
        $this->assertEquals($expectedScheme, $request->uri->scheme, $message);
    }

    public function testCommaInHeaderThatDoesNotPermitMultipleValuesDoesNotSplitHeaderValue(): void
    {
        $request = $this->factory->createRequestFromSuperglobals([
            'HTTP_FOO' => 'text/html,application/xhtml+xml',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertEquals(['text/html,application/xhtml+xml'], $request->headers->get('Foo'));
    }

    public function testCookiesAreAddedToHeaders(): void
    {
        $request = $this->factory->createRequestFromSuperglobals([
            'HTTP_COOKIE' => 'foo=bar; baz=blah',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertSame('foo=bar; baz=blah', $request->headers->getFirst('Cookie'));
    }

    public function testExceptionThrownWhenUsingUntrustedProxyHost(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid host "192.168.1.1, 192.168.1.2, 192.168.1.3"');
        $this->factory->createRequestFromSuperglobals(['HTTP_HOST' => '192.168.1.1, 192.168.1.2, 192.168.1.3']);
    }

    public function testForwardedHostUsedWithTrustedProxy(): void
    {
        $factory = new RequestFactory(['192.168.2.1']);
        $server = ['REMOTE_ADDR' => '192.168.2.1', 'HTTP_X_FORWARDED_HOST' => 'foo.com, bar.com'];
        $request = $factory->createRequestFromSuperglobals($server);
        $this->assertSame('bar.com', $request->uri->host);
    }

    public function testHostWithPortStripsPortFromHost(): void
    {
        $request = $this->factory->createRequestFromSuperglobals(['HTTP_HOST' => 'foo.com:8080']);
        $this->assertSame('foo.com', $request->uri->host);
    }

    /**
     * @param array<string, mixed> $superglobals The superglobals to create the request from
     * @param string $expectedScheme The expected scheme
     */
    #[DataProvider('httpServerPropertyProvider')]
    public function testHttpsServerPropertyControlsSchemeOfUri(array $superglobals, string $expectedScheme): void
    {
        $httpsOnRequest = $this->factory->createRequestFromSuperglobals($superglobals);
        $this->assertEquals($expectedScheme, $httpsOnRequest->uri->scheme);
    }

    public function testInputMethodIsSetInRequest(): void
    {
        $request = $this->factory->createRequestFromSuperglobals([
            'REQUEST_METHOD' => 'CONNECT',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertSame('CONNECT', $request->method);
    }

    public function testInvalidHostCharThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid host "!"');
        $this->factory->createRequestFromSuperglobals(['HTTP_HOST' => '!']);
    }

    public function testMethodOverrideHeaderOverridesInputMethodForPostRequests(): void
    {
        $request = $this->factory->createRequestFromSuperglobals([
            'REQUEST_METHOD' => 'POST',
            'X-HTTP-METHOD-OVERRIDE' => 'PUT',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertSame('PUT', $request->method);
    }

    public function testMultipleHeaderValuesAreAppended(): void
    {
        $request = $this->factory->createRequestFromSuperglobals([
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertEquals(['text/html', 'application/xhtml+xml'], $request->headers->get('Accept'));
    }

    /**
     * @param string $name The name of the header value to add
     * @param mixed $value The header value that should not appear
     */
    #[TestWith(['foo', ['baz']])]
    #[TestWith(['HTTP_FOO', ['baz']])]
    public function testNonScalarHeaderValuesAreNotAddedToCollection(string $name, mixed $value): void
    {
        $request = $this->factory->createRequestFromSuperglobals([
            $name => $value,
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertFalse($request->headers->containsKey($name));
    }

    public function testPathWithQueryStringStripsTheQueryStringFromUriPath(): void
    {
        $request = $this->factory->createRequestFromSuperglobals([
            'REQUEST_URI' => '/foo/bar?baz=blah',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertSame('/foo/bar', $request->uri->path);
    }

    public function testPortHeaderSetsPortOnUri(): void
    {
        $request = $this->factory->createRequestFromSuperglobals([
            'SERVER_PORT' => 8080,
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertSame(8080, $request->uri->port);
    }

    public function testQueryStringServerPropertyIsUsedBeforeRequestUriQueryString(): void
    {
        $request = $this->factory->createRequestFromSuperglobals([
            'QUERY_STRING' => '?foo=bar',
            'REQUEST_URI' => '/baz?blah=dave',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertSame('foo=bar', $request->uri->queryString);
    }

    public function testQuotedCommaInHeaderRemainsIntact(): void
    {
        $request = $this->factory->createRequestFromSuperglobals([
            'HTTP_ACCEPT' => 'foo/bar;p="A,B",baz',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertEquals(['foo/bar;p="A,B"', 'baz'], $request->headers->get('Accept'));
    }

    public function testRemoteAddrUsedAsIpAddressWhenNotUsingTrustedProxy(): void
    {
        $request = $this->factory->createRequestFromSuperglobals([
            'REMOTE_ADDR' => '192.168.2.1',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertSame('192.168.2.1', $request->properties->get('CLIENT_IP_ADDRESS'));
    }

    public function testRequestUriQueryStringIsUsedIfQueryStringServerPropertyDoesNotExist(): void
    {
        $request = $this->factory->createRequestFromSuperglobals([
            'REQUEST_URI' => '/foo?bar=baz',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertSame('bar=baz', $request->uri->queryString);
    }

    public function testSpecialCaseHeadersAreAddedToRequestHeaders(): void
    {
        $server = [
            'AUTH_TYPE' => 'auth_type',
            'CONTENT_LENGTH' => 123,
            'CONTENT_TYPE' => 'content_type',
            'PHP_AUTH_DIGEST' => 'php_auth_digest',
            'PHP_AUTH_PW' => 'php_auth_pw',
            'PHP_AUTH_TYPE' => 'php_auth_type',
            'PHP_AUTH_USER' => 'php_auth_user',
            'HTTP_HOST' => 'foo.com'
        ];
        $expectedHeaders = [
            'Auth-Type' => ['auth_type'],
            'Content-Length' => [123],
            'Content-Type' => ['content_type'],
            'Php-Auth-Digest' => ['php_auth_digest'],
            'Php-Auth-Pw' => ['php_auth_pw'],
            'Php-Auth-Type' => ['php_auth_type'],
            'Php-Auth-User' => ['php_auth_user']
        ];
        $headers = $this->factory->createRequestFromSuperglobals($server)->headers;

        foreach ($expectedHeaders as $expectedName => $expectedValue) {
            $this->assertEquals($expectedValue, $headers->get($expectedName));
        }
    }
}
