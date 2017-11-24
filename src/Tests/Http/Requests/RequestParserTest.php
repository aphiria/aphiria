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
use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\IHttpBody;
use Opulence\Net\Http\Requests\IHttpRequestMessage;
use Opulence\Net\Http\Requests\RequestParser;

/**
 * Tests the HTTP request message parser
 */
class RequestParserTest extends \PHPUnit\Framework\TestCase
{
    /** @var RequestParser The parser to use in tests */
    private $parser = null;
    /** @var IHttpRequestMessage|\PHPUnit_Framework_MockObject_MockObject The request message to use in tests */
    private $request = null;
    /** @var HttpHeaders The headers to use in tests */
    private $headers = null;
    /** @var IHttpBody|\PHPUnit_Framework_MockObject_MockObject The body to use in tests */
    private $body = null;
    /** @var IDictionary|\PHPUnit_Framework_MockObject_MockObject The request properties to use in tests */
    private $properties = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->parser = new RequestParser();
        $this->headers = new HttpHeaders();
        $this->body = $this->createMock(IHttpBody::class);
        $this->properties = new HashTable();
        $this->request = $this->createMock(IHttpRequestMessage::class);
        $this->request->expects($this->any())
            ->method('getHeaders')
            ->willReturn($this->headers);
        $this->request->expects($this->any())
            ->method('getBody')
            ->willReturn($this->body);
        $this->request->expects($this->any())
            ->method('getProperties')
            ->willReturn($this->properties);
    }

    /**
     * Tests that getting the client IP address returns null when the property is not set
     */
    public function testGettingClientIPAddressReturnsNullWhenPropertyIsNotSet() : void
    {
        $this->assertNull($this->parser->getClientIPAddress($this->request));
    }

    /**
     * Tests that getting the client IP address returns the property value when the property is set
     */
    public function testGettingClientIPAddressReturnsPropertyValueWhenPropertyIsSet() : void
    {
        $this->properties->add('CLIENT_IP_ADDRESS', '127.0.0.1');
        $this->assertEquals('127.0.0.1', $this->parser->getClientIPAddress($this->request));
    }

    /**
     * Tests getting the mime type a non-request and non-multipart body part throws an exception
     */
    public function testGettingMimeTypeOfNonRequestNorMultipartBodyPartThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->parser->getMimeType([]);
    }

    /**
     * Tests parsing a multipart request without a boundary throws an exception
     */
    public function testParsingMultipartRequestWithoutBoundaryThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->headers->add('Content-Type', 'multipart/mixed');
        $this->parser->readAsMultipart($this->request);
    }

    /**
     * Tests parsing a non-request and non-multipart body part throws an exception
     */
    public function testParsingNonRequestNorMultipartBodyPartThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->parser->readAsMultipart([]);
    }
}
