<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting;

use InvalidArgumentException;
use Opulence\Collections\HashTable;
use Opulence\Collections\IDictionary;
use Opulence\Net\Http\Formatting\RequestParser;
use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\IHttpBody;
use Opulence\Net\Http\IHttpRequestMessage;

/**
 * Tests the HTTP request message parser
 */
class RequestParserTest extends \PHPUnit\Framework\TestCase
{
    /** @var RequestParser The parser to use in tests */
    private $parser;
    /** @var IHttpRequestMessage|\PHPUnit_Framework_MockObject_MockObject The request message to use in tests */
    private $request;
    /** @var HttpHeaders The headers to use in tests */
    private $headers;
    /** @var IHttpBody|\PHPUnit_Framework_MockObject_MockObject The body to use in tests */
    private $body;
    /** @var IDictionary|\PHPUnit_Framework_MockObject_MockObject The request properties to use in tests */
    private $properties;

    public function setUp(): void
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

    public function testGettingClientIPAddressReturnsNullWhenPropertyIsNotSet(): void
    {
        $this->assertNull($this->parser->getClientIPAddress($this->request));
    }

    public function testGettingClientIPAddressReturnsPropertyValueWhenPropertyIsSet(): void
    {
        $this->properties->add('CLIENT_IP_ADDRESS', '127.0.0.1');
        $this->assertEquals('127.0.0.1', $this->parser->getClientIPAddress($this->request));
    }

    public function testGettingMimeTypeOfNonRequestNorMultipartBodyPartThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->parser->getMimeType([]);
    }

    public function testParsingMultipartRequestWithoutBoundaryThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->headers->add('Content-Type', 'multipart/mixed');
        $this->parser->readAsMultipart($this->request);
    }

    public function testParsingNonRequestNorMultipartBodyPartThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->parser->readAsMultipart([]);
    }
}
