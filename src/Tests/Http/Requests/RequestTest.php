<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Requests;

use InvalidArgumentException;
use Opulence\Collections\HashTable;
use Opulence\Collections\KeyValuePair;
use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\IHttpBody;
use Opulence\Net\Http\Requests\Request;
use Opulence\Net\Uri;

/**
 * Tests the request
 */
class RequestTest extends \PHPUnit\Framework\TestCase
{
    /** @var Request The request to use in tests */
    private $request = null;
    /** @var HttpHeaders The headers */
    private $headers = null;
    /** @var IHttpBody|\PHPUnit_Framework_MockObject_MockObject The mock body */
    private $body = null;
    /** @var Uri The request URI */
    private $uri = null;
    /** @var HashTable The request properties */
    private $properties = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->headers = new HttpHeaders();
        $this->body = $this->createMock(IHttpBody::class);
        $this->uri = new Uri('http', null, null, 'host', null, '', null, null);
        $this->properties = new HashTable([new KeyValuePair('foo', 'bar')]);
        $this->request = new Request(
            'GET',
            $this->uri,
            $this->headers,
            $this->body,
            $this->properties,
            '2.0'
        );
    }

    /**
     * Tests getting the body
     */
    public function testGettingBody() : void
    {
        $this->assertSame($this->body, $this->request->getBody());
    }

    /**
     * Tests getting the headers
     */
    public function testGettingHeaders() : void
    {
        $this->assertSame($this->headers, $this->request->getHeaders());
    }

    /**
     * Tests getting the method
     */
    public function testGettingMethod() : void
    {
        $this->assertSame('GET', $this->request->getMethod());
    }

    /**
     * Tests getting properties
     */
    public function testGettingProperties() : void
    {
        $this->assertSame($this->properties, $this->request->getProperties());
    }

    /**
     * Tests getting the protocol version
     */
    public function testGettingProtocolVersion() : void
    {
        $this->assertEquals('2.0', $this->request->getProtocolVersion());
    }

    /**
     * Tests getting the method returns the method set in the headers
     */
    public function testGettingUri() : void
    {
        $this->assertSame($this->uri, $this->request->getUri());
    }

    /**
     * Tests setting the body
     */
    public function testSettingBody() : void
    {
        $body = $this->createMock(IHttpBody::class);
        $this->request->setBody($body);
        $this->assertSame($body, $this->request->getBody());
    }

    /**
     * Tests setting an invalid method throws an exception
     */
    public function testSettingInvalidMethodThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new Request('foo', $this->uri, $this->headers, $this->body);
    }
}
