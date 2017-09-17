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
use Opulence\Net\Http\IHttpBody;
use Opulence\Net\Http\IHttpHeaders;
use Opulence\Net\Uri;

/**
 * Tests the request
 */
class RequestTest extends \PHPUnit\Framework\TestCase
{
    /** @var Request The request to use in tests */
    private $request = null;
    /** @var IHttpHeaders|\PHPUnit_Framework_MockObject_MockObject The mock headers */
    private $headers = null;
    /** @var IHttpBody|\PHPUnit_Framework_MockObject_MockObject The mock body */
    private $body = null;
    /** @var Uri The request URI */
    private $uri = null;
    /** @var array The request properties */
    private $properties = [];
    
    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->headers = $this->createMock(IHttpHeaders::class);
        $this->body = $this->createMock(IHttpBody::class);
        $this->uri = new Uri('http', null, null, 'host', null, null, null);
        $this->properties = ['foo' => 'bar'];
        $this->request = new Request(
            'GET',
            $this->headers,
            $this->body,
            $this->uri,
            $this->properties
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
        $this->request->setMethod('foo');
    }
    
    /**
     * Tests setting the method
     */
    public function testSettingMethod() : void
    {
        $this->request->setMethod('DELETE');
        $this->assertEquals('DELETE', $this->request->getMethod());
    }
    
    /**
     * Tests setting the URI
     */
    public function testSettingUri() : void
    {
        $uri = new Uri('http', null, null, 'host', null, null, null);
        $this->request->setUri($uri);
        $this->assertSame($uri, $this->request->getUri());
    }
}
