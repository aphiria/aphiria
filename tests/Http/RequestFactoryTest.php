<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/aphiria/net/blob/master/LICENSE.md
 */

namespace Aphiria\Net\Tests\Http;

use Aphiria\Net\Http\RequestFactory;
use Aphiria\Net\Http\StreamBody;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the request factory
 */
class RequestFactoryTest extends TestCase
{
    /** @var RequestFactory The request factory to use in tests */
    private $factory;

    protected function setUp(): void
    {
        $this->factory = new RequestFactory();
    }

    public function testAuthorityInServerSetsAuthorityInUri(): void
    {
        $request = $this->factory->createRequestFromSuperglobals([
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW' => 'pw',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertEquals('user', $request->getUri()->getUser());
        $this->assertEquals('pw', $request->getUri()->getPassword());
    }

    public function testBodyIsCreatedFromInputStream(): void
    {
        $request = $this->factory->createRequestFromSuperglobals(['HTTP_HOST' => 'foo.com']);
        $this->assertInstanceOf(StreamBody::class, $request->getBody());
    }

    public function testCertainHeaderValuesAreUrlDecoded(): void
    {
        // Only cookies should be decoded
        $request = $this->factory->createRequestFromSuperglobals([
            'HTTP_FOO' => '%25',
            'HTTP_COOKIE' => '%25',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertEquals('%25', $request->getHeaders()->getFirst('Foo'));
        $this->assertEquals('%', $request->getHeaders()->getFirst('Cookie'));
    }

    public function testClientIPAddressIsSetAsPropertyWhenUsingTrustedProxy(): void
    {
        $factory = new RequestFactory(['192.168.1.1']);
        $request = $factory->createRequestFromSuperglobals([
            'REMOTE_ADDR' => '192.168.1.1',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertEquals('192.168.1.1', $request->getProperties()->get('CLIENT_IP_ADDRESS'));
    }

    public function clientIPDataProvider(): array
    {
        return [
            ['for="_gazonk"', '_gazonk'],
            ['for="[2001:db8:cafe::17]:4711"', '2001:db8:cafe::17'],
            ['for=192.0.2.60;proto=http;by=203.0.113.43', '192.0.2.60'],
            ['for=192.0.2.43, for=198.51.100.17', '198.51.100.17'],
        ];
    }

    /**
     * @dataProvider clientIPDataProvider
     */
    public function testClientIPAddressIsSetFromForwardedHeaderWhenUsingTrustedProxy($ipDatum1, $expectedIpDatum): void
    {
        $_SERVER['HTTP_FORWARDED'] = $ipDatum1;
        $factory = new RequestFactory([], ['HTTP_FORWARDED' => 'HTTP_FORWARDED']);
        $request = $factory->createRequestFromSuperglobals([
            'HTTP_FORWARDED' => $ipDatum1,
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertEquals($expectedIpDatum, $request->getProperties()->get('CLIENT_IP_ADDRESS'));
    }

    public function testClientIPHeaderUsedWhenSet(): void
    {
        $factory = new RequestFactory([], ['HTTP_CLIENT_IP' => 'HTTP_CLIENT_IP', 'HTTP_FORWARDED' => 'FORWARDED']);
        $request = $factory->createRequestFromSuperglobals([
            'HTTP_CLIENT_IP' => '192.168.1.1',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertEquals('192.168.1.1', $request->getProperties()->get('CLIENT_IP_ADDRESS'));
    }

    public function testClientPortUsedToDeterminePortWithTrustedProxy(): void
    {
        $factory = new RequestFactory(['192.168.1.1'], ['HTTP_CLIENT_PORT' => 'HTTP_X_FORWARDED_PORT']);
        $request = $factory->createRequestFromSuperglobals([
            'REMOTE_ADDR' => '192.168.1.1',
            'HTTP_X_FORWARDED_PORT' => 8080,
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertEquals(8080, $request->getUri()->getPort());
    }

    public function clientProtoProvider(): array
    {
        return [
            ['192.168.1.1', 'HTTPS', 'foo.com', 'https', 'Try with HTTPS'],
            ['192.168.1.1', 'ssl', 'foo.com', 'https', 'Try with SSL'],
            ['192.168.1.1', 'on', 'foo.com', 'https', 'Try with "on"'],
            ['192.168.1.1', 'HTTP', 'foo.com', 'http', 'Try with HTTP'],
        ];
    }

    /**
     * @dataProvider clientProtoProvider
     */
    public function testClientProtoUsedToSetSchemeWithTrustedProxy($remoteAddress, $forwaredProto, $host, $expectedScheme, $message): void
    {
        $factory = new RequestFactory(['192.168.1.1'], ['HTTP_CLIENT_PROTO' => 'HTTP_X_FORWARDED_PROTO']);
        $request = $factory->createRequestFromSuperglobals([
            'REMOTE_ADDR' => $remoteAddress,
            'HTTP_X_FORWARDED_PROTO' => $forwaredProto,
            'HTTP_HOST' => $host,
        ]);
        $this->assertEquals($expectedScheme, $request->getUri()->getScheme(), $message);
    }

    public function testClientProtoUsedToDeterminePortWithTrustedProxy(): void
    {
        $factory = new RequestFactory(['192.168.1.1'], ['HTTP_CLIENT_PROTO' => 'HTTP_X_FORWARDED_PROTO']);
        $request = $factory->createRequestFromSuperglobals([
            'REMOTE_ADDR' => '192.168.1.1',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertEquals(443, $request->getUri()->getPort());
    }

    public function testCommaInHeaderThatDoesNotPermitMultipleValuesDoesNotSplitHeaderValue(): void
    {
        $request = $this->factory->createRequestFromSuperglobals([
            'HTTP_FOO' => 'text/html,application/xhtml+xml',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertEquals(['text/html,application/xhtml+xml'], $request->getHeaders()->get('Foo'));
    }

    public function testCookiesAreAddedToHeaders(): void
    {
        $request = $this->factory->createRequestFromSuperglobals([
            'HTTP_COOKIE' => 'foo=bar; baz=blah',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertEquals('foo=bar; baz=blah', $request->getHeaders()->getFirst('Cookie'));
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
        $this->assertEquals('bar.com', $request->getUri()->getHost());
    }

    public function testHostWithPortStripsPortFromHost(): void
    {
        $request = $this->factory->createRequestFromSuperglobals(['HTTP_HOST' => 'foo.com:8080']);
        $this->assertEquals('foo.com', $request->getUri()->getHost());
    }

    public function httpServerPropertyProvider(): array
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

    /**
     * @dataProvider httpServerPropertyProvider
     */
    public function testHttpsServerPropertyControlsSchemeOfUri($properties, $expectedScheme): void
    {
        $httpsOnRequest = $this->factory->createRequestFromSuperglobals($properties);
        $this->assertEquals($expectedScheme, $httpsOnRequest->getUri()->getScheme());
    }

    public function testInputMethodIsSetInRequest(): void
    {
        $request = $this->factory->createRequestFromSuperglobals([
            'REQUEST_METHOD' => 'CONNECT',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertEquals('CONNECT', $request->getMethod());
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
        $this->assertEquals('PUT', $request->getMethod());
    }

    public function testMultipleHeaderValuesAreAppended(): void
    {
        $request = $this->factory->createRequestFromSuperglobals([
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertEquals(['text/html', 'application/xhtml+xml'], $request->getHeaders()->get('Accept'));
    }

    public function testPathWithQueryStringStripsTheQueryStringFromUriPath(): void
    {
        $request = $this->factory->createRequestFromSuperglobals([
            'REQUEST_URI' => '/foo/bar?baz=blah',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertEquals('/foo/bar', $request->getUri()->getPath());
    }

    public function testPortHeaderSetsPortOnUri(): void
    {
        $request = $this->factory->createRequestFromSuperglobals([
            'SERVER_PORT' => 8080,
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertEquals(8080, $request->getUri()->getPort());
    }

    public function testQueryStringServerPropertyIsUsedBeforeRequestUriQueryString(): void
    {
        $request = $this->factory->createRequestFromSuperglobals([
            'QUERY_STRING' => '?foo=bar',
            'REQUEST_URI' => '/baz?blah=dave',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertEquals('foo=bar', $request->getUri()->getQueryString());
    }

    public function testQuotedCommaInHeaderRemainsIntact(): void
    {
        $request = $this->factory->createRequestFromSuperglobals([
            'HTTP_ACCEPT' => 'foo/bar;p="A,B",baz',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertEquals(['foo/bar;p="A,B"', 'baz'], $request->getHeaders()->get('Accept'));
    }

    public function testRequestUriQueryStringIsUsedIfQueryStringServerPropertyDoesNotExist(): void
    {
        $request = $this->factory->createRequestFromSuperglobals([
            'REQUEST_URI' => '/foo?bar=baz',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertEquals('bar=baz', $request->getUri()->getQueryString());
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
        $headers = $this->factory->createRequestFromSuperglobals($server)->getHeaders();

        foreach ($expectedHeaders as $expectedName => $expectedValue) {
            $this->assertEquals($expectedValue, $headers->get($expectedName));
        }
    }
}
