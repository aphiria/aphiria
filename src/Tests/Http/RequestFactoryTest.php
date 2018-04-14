<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http;

use InvalidArgumentException;
use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\RequestFactory;
use Opulence\Net\Http\StreamBody;

/**
 * Tests the request factory
 */
class RequestFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var RequestFactory The request factory to use in tests */
    private $factory;

    public function setUp(): void
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

    public function testClientIPAddressIsSetFromForwardedHeaderWhenUsingTrustedProxy(): void
    {
        $ipData = [
            ['for="_gazonk"', '_gazonk'],
            ['for="[2001:db8:cafe::17]:4711"', '2001:db8:cafe::17'],
            ['for=192.0.2.60;proto=http;by=203.0.113.43', '192.0.2.60'],
            ['for=192.0.2.43, for=198.51.100.17', '198.51.100.17']
        ];

        foreach ($ipData as $ipDatum) {
            $_SERVER['HTTP_FORWARDED'] = $ipDatum[0];
            $factory = new RequestFactory([], ['HTTP_FORWARDED' => 'HTTP_FORWARDED']);
            $request = $factory->createRequestFromSuperglobals([
                'HTTP_FORWARDED' => $ipDatum[0],
                'HTTP_HOST' => 'foo.com'
            ]);
            $this->assertEquals($ipDatum[1], $request->getProperties()->get('CLIENT_IP_ADDRESS'));
        }
    }

    public function testClientIPHeaderUsedWhenSet()
    {
        $factory = new RequestFactory([], ['HTTP_CLIENT_IP' => 'HTTP_CLIENT_IP', 'HTTP_FORWARDED' => 'FORWARDED']);
        $request = $factory->createRequestFromSuperglobals([
            'HTTP_CLIENT_IP' => '192.168.1.1',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertEquals('192.168.1.1', $request->getProperties()->get('CLIENT_IP_ADDRESS'));
    }

    public function testClientPortUsedToDeterminePortWithTrustedProxy()
    {
        $factory = new RequestFactory(['192.168.1.1'], ['HTTP_CLIENT_PORT' => 'HTTP_X_FORWARDED_PORT']);
        $request = $factory->createRequestFromSuperglobals([
            'REMOTE_ADDR' => '192.168.1.1',
            'HTTP_X_FORWARDED_PORT' => 8080,
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertEquals(8080, $request->getUri()->getPort());
    }

    public function testClientProtoUsedToSetSchemeWithTrustedProxy()
    {
        // Try with HTTPS
        $factory = new RequestFactory(['192.168.1.1'], ['HTTP_CLIENT_PROTO' => 'HTTP_X_FORWARDED_PROTO']);
        $request = $factory->createRequestFromSuperglobals([
            'REMOTE_ADDR' => '192.168.1.1',
            'HTTP_X_FORWARDED_PROTO' => 'HTTPS',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertEquals('https', $request->getUri()->getScheme());

        // Try with SSL
        $request = $factory->createRequestFromSuperglobals([
            'REMOTE_ADDR' => '192.168.1.1',
            'HTTP_X_FORWARDED_PROTO' => 'ssl',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertEquals('https', $request->getUri()->getScheme());

        // Try with "on"
        $request = $factory->createRequestFromSuperglobals([
            'REMOTE_ADDR' => '192.168.1.1',
            'HTTP_X_FORWARDED_PROTO' => 'on',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertEquals('https', $request->getUri()->getScheme());

        // Try with HTTP
        $request = $factory->createRequestFromSuperglobals([
            'REMOTE_ADDR' => '192.168.1.1',
            'HTTP_X_FORWARDED_PROTO' => 'HTTP',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertEquals('http', $request->getUri()->getScheme());
    }

    public function testClientProtoUsedToDeterminePortWithTrustedProxy()
    {
        $factory = new RequestFactory(['192.168.1.1'], ['HTTP_CLIENT_PROTO' => 'HTTP_X_FORWARDED_PROTO']);
        $request = $factory->createRequestFromSuperglobals([
            'REMOTE_ADDR' => '192.168.1.1',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertEquals(443, $request->getUri()->getPort());
    }

    public function testCookiesAreAddedToHeaders(): void
    {
        $request = $this->factory->createRequestFromSuperglobals([
            'HTTP_COOKIE' => 'foo=bar; baz=blah',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertEquals('foo=bar; baz=blah', $request->getHeaders()->getFirst('Cookie'));
    }

    public function testExceptionThrownWhenUsingUntrustedProxyHost()
    {
        $this->expectException(InvalidArgumentException::class);
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

    public function testHttpsServerPropertyControlsSchemeOfUri(): void
    {
        $httpsOnRequest = $this->factory->createRequestFromSuperglobals([
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertEquals('https', $httpsOnRequest->getUri()->getScheme());
        $httpsOffRequest = $this->factory->createRequestFromSuperglobals([
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'HTTPS' => 'off',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertEquals('http', $httpsOffRequest->getUri()->getScheme());
        $noHttpsRequest = $this->factory->createRequestFromSuperglobals([
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'HTTP_HOST' => 'foo.com'
        ]);
        $this->assertEquals('http', $noHttpsRequest->getUri()->getScheme());
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
        $this->assertInstanceOf(HttpHeaders::class, $headers);

        foreach ($expectedHeaders as $expectedName => $expectedValue) {
            $this->assertEquals($expectedValue, $headers->get($expectedName));
        }
    }
}
