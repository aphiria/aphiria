<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Requests;

use InvalidArgumentException;
use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\StreamBody;
use Opulence\Net\Http\StringBody;

/**
 * Tests the request factory
 */
class RequestFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var RequestFactory The request factory to use in tests */
    private $factory = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->factory = new RequestFactory();
    }

    /**
     * Tests that the authority in the server sets the authority in the URI
     */
    public function testAuthorityInServerSetsAuthorityInUri() : void
    {
        $request = $this->factory->createFromGlobals(['PHP_AUTH_USER' => 'user', 'PHP_AUTH_PW' => 'pw']);
        $this->assertEquals('user', $request->getUri()->getUser());
        $this->assertEquals('pw', $request->getUri()->getPassword());
    }

    /**
     * Tests that the client IP address is set as a property when using a trusted proxy
     */
    public function testClientIPAddressIsSetAsPropertyWhenUsingTrustedProxy() : void
    {
        $factory = new RequestFactory(['192.168.1.1']);
        $request = $factory->createFromGlobals(['REMOTE_ADDR' => '192.168.1.1']);
        $this->assertEquals('192.168.1.1', $request->getProperties()->get('CLIENT_IP_ADDRESS'));
    }

    /**
     * Tests that the client IP address is set as a property when using a trusted proxy
     */
    public function testClientIPAddressIsSetFromForwardedHeaderWhenUsingTrustedProxy() : void
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
            $request = $factory->createFromGlobals(['HTTP_FORWARDED' => $ipDatum[0]]);
            $this->assertEquals($ipDatum[1], $request->getProperties()->get('CLIENT_IP_ADDRESS'));
        }
    }

    /**
     * Tests that the client IP header is used when set
     */
    public function testClientIPHeaderUsedWhenSet()
    {
        $factory = new RequestFactory([], ['HTTP_CLIENT_IP' => 'HTTP_CLIENT_IP', 'HTTP_FORWARDED' => 'FORWARDED']);
        $request = $factory->createFromGlobals(['HTTP_CLIENT_IP' => '192.168.1.1']);
        $this->assertEquals('192.168.1.1', $request->getProperties()->get('CLIENT_IP_ADDRESS'));
    }

    /**
     * Tests that the client port is used with a trusted proxy
     */
    public function testClientPortUsedToDeterminePortWithTrustedProxy()
    {
        $factory = new RequestFactory(['192.168.1.1'], ['HTTP_CLIENT_PORT' => 'HTTP_X_FORWARDED_PORT']);
        $request = $factory->createFromGlobals(['REMOTE_ADDR' => '192.168.1.1', 'HTTP_X_FORWARDED_PORT' => 8080]);
        $this->assertEquals(8080, $request->getUri()->getPort());
    }

    /**
     * Tests that the client proto is used to set the scheme a trusted proxy
     */
    public function testClientProtoUsedToSetSchemeWithTrustedProxy()
    {
        // Try with HTTPS
        $factory = new RequestFactory(['192.168.1.1'], ['HTTP_CLIENT_PROTO' => 'HTTP_X_FORWARDED_PROTO']);
        $request = $factory->createFromGlobals(['REMOTE_ADDR' => '192.168.1.1', 'HTTP_X_FORWARDED_PROTO' => 'HTTPS']);
        $this->assertEquals('https', $request->getUri()->getScheme());

        // Try with SSL
        $request = $factory->createFromGlobals(['REMOTE_ADDR' => '192.168.1.1', 'HTTP_X_FORWARDED_PROTO' => 'ssl']);
        $this->assertEquals('https', $request->getUri()->getScheme());

        // Try with "on"
        $request = $factory->createFromGlobals(['REMOTE_ADDR' => '192.168.1.1', 'HTTP_X_FORWARDED_PROTO' => 'on']);
        $this->assertEquals('https', $request->getUri()->getScheme());

        // Try with HTTP
        $request = $factory->createFromGlobals(['REMOTE_ADDR' => '192.168.1.1', 'HTTP_X_FORWARDED_PROTO' => 'HTTP']);
        $this->assertEquals('http', $request->getUri()->getScheme());
    }

    /**
     * Tests that the client proto is used with a trusted proxy
     */
    public function testClientProtoUsedToDeterminePortWithTrustedProxy()
    {
        $factory = new RequestFactory(['192.168.1.1'], ['HTTP_CLIENT_PROTO' => 'HTTP_X_FORWARDED_PROTO']);
        $request = $factory->createFromGlobals(['REMOTE_ADDR' => '192.168.1.1', 'HTTP_X_FORWARDED_PROTO' => 'https']);
        $this->assertEquals(443, $request->getUri()->getPort());
    }

    /**
     * Tests that the cookies are added to headers
     */
    public function testCookiesAreAddedToHeaders() : void
    {
        $request = $this->factory->createFromGlobals(['HTTP_COOKIE' => 'foo=bar; baz=blah']);
        $this->assertEquals('foo=bar; baz=blah', $request->getHeaders()->get('Cookie'));
    }

    /**
     * Tests that an empty raw body creates body from the input stream
     */
    public function testEmptyRawBodyCreatesBodyFromInputStream() : void
    {
        $request = $this->factory->createFromGlobals();
        $this->assertInstanceOf(StreamBody::class, $request->getBody());
    }

    /**
     * Tests that an exception is thrown when using an untrusted proxy host
     */
    public function testExceptionThrownWhenUsingUntrustedProxyHost()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->factory->createFromGlobals(['HTTP_HOST' => '192.168.1.1, 192.168.1.2, 192.168.1.3']);
    }

    /**
     * Tests that the forwarded host is used with a trusted proxy
     */
    public function testForwardedHostUsedWithTrustedProxy() : void
    {
        $factory = new RequestFactory(['192.168.2.1']);
        $server = ['REMOTE_ADDR' => '192.168.2.1', 'HTTP_X_FORWARDED_HOST' => 'foo.com, bar.com'];
        $request = $factory->createFromGlobals($server);
        $this->assertEquals('bar.com', $request->getUri()->getHost());
    }
    
    /**
     * Tests that header values are decoded
     */
    public function testHeaderValuesAreDecoded() : void
    {
        $request = $this->factory->createFromGlobals(['HTTP_FOO' => '%25']);
        $this->assertEquals('%', $request->getHeaders()->get('Foo'));
    }

    /**
     * Tests that a host with a port strips the port from the host
     */
    public function testHostWithPortStripsPortFromHost() : void
    {
        $request = $this->factory->createFromGlobals(['HTTP_HOST' => 'foo.com:8080']);
        $this->assertEquals('foo.com', $request->getUri()->getHost());
    }

    /**
     * Tests that the HTTPS server property controls the scheme of the URI
     */
    public function testHttpsServerPropertyControlsSchemeOfUri() : void
    {
        $httpsOnRequest = $this->factory->createFromGlobals(['SERVER_PROTOCOL' => 'HTTP/1.1', 'HTTPS' => 'on']);
        $this->assertEquals('https', $httpsOnRequest->getUri()->getScheme());
        $httpsOffRequest = $this->factory->createFromGlobals(['SERVER_PROTOCOL' => 'HTTP/1.1', 'HTTPS' => 'off']);
        $this->assertEquals('http', $httpsOffRequest->getUri()->getScheme());
        $noHttpsRequest = $this->factory->createFromGlobals(['SERVER_PROTOCOL' => 'HTTP/1.1']);
        $this->assertEquals('http', $noHttpsRequest->getUri()->getScheme());
    }

    /**
     * Tests that the input method is set in the request
     */
    public function testInputMethodIsSetInRequest() : void
    {
        $request = $this->factory->createFromGlobals(['REQUEST_METHOD' => 'CONNECT']);
        $this->assertEquals('CONNECT', $request->getMethod());
    }

    /**
     * Tests that an invalid host char throws an exception
     */
    public function testInvalidHostCharThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->factory->createFromGlobals(['HTTP_HOST' => '!']);
    }

    /**
     * Tests that the method override header overrides the input method for post requests
     */
    public function testMethodOverrideHeaderOverridesInputMethodForPostRequests() : void
    {
        $request = $this->factory->createFromGlobals(
            ['REQUEST_METHOD' => 'POST', 'X-HTTP-METHOD-OVERRIDE' => 'PUT']
        );
        $this->assertEquals('PUT', $request->getMethod());
    }

    /**
     * Tests that a path with a query string strips the query strip from the URI path
     */
    public function testPathWithQueryStringStripsTheQueryStringFromUriPath() : void
    {
        $request = $this->factory->createFromGlobals(['REQUEST_URI' => '/foo/bar?baz=blah']);
        $this->assertEquals('/foo/bar', $request->getUri()->getPath());
    }

    /**
     * Tests that the port header sets the port on the URI
     */
    public function testPortHeaderSetsPortOnUri() : void
    {
        $request = $this->factory->createFromGlobals(['SERVER_PORT' => 8080]);
        $this->assertEquals(8080, $request->getUri()->getPort());
    }

    /**
     * Tests that the query string server property is used before the request URI's query string is used
     */
    public function testQueryStringServerPropertyIsUsedBeforeRequestUriQueryString() : void
    {
        $request = $this->factory->createFromGlobals(['QUERY_STRING' => '?foo=bar', 'REQUEST_URI' => '/baz?blah=dave']);
        $this->assertEquals('foo=bar', $request->getUri()->getQueryString());
    }

    /**
     * Tests that the request URI's query string is used if the query string server property does not exist
     */
    public function testRequestUriQueryStringIsUsedIfQueryStringServerPropertyDoesNotExist() : void
    {
        $request = $this->factory->createFromGlobals(['REQUEST_URI' => '/foo?bar=baz']);
        $this->assertEquals('bar=baz', $request->getUri()->getQueryString());
    }

    /**
     * Tests that the special-case headers are added to request headers
     */
    public function testSpecialCaseHeadersAreAddedToRequestHeaders() : void
    {
        $server = [
            'AUTH_TYPE' => 'auth_type',
            'CONTENT_LENGTH' => 123,
            'CONTENT_TYPE' => 'content_type',
            'PHP_AUTH_DIGEST' => 'php_auth_digest',
            'PHP_AUTH_PW' => 'php_auth_pw',
            'PHP_AUTH_TYPE' => 'php_auth_type',
            'PHP_AUTH_USER' => 'php_auth_user'
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
        $headers = $this->factory->createFromGlobals($server)->getHeaders();
        $this->assertInstanceOf(HttpHeaders::class, $headers);
        $this->assertEquals($expectedHeaders, $headers->getAll());
    }

    /**
     * Tests that an string raw body creates body from that string
     */
    public function testStringRawBodyCreatesBodyFromThatString() : void
    {
        $request = $this->factory->createFromGlobals([], 'foo');
        $this->assertInstanceOf(StringBody::class, $request->getBody());
        $this->assertEquals('foo', $request->getBody()->readAsString());
    }
}
