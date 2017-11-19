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
     * Tests parsing a multipart request extracts the headers
     */
    public function testParsingMultipartRequestExtractsHeaders() : void
    {
        $this->headers->add('Content-Type', 'multipart/mixed; boundary="boundary"');
        $this->body->expects($this->once())
            ->method('readAsString')
            ->willReturn("--boundary\r\nFoo: bar\r\nBaz: blah\r\n\r\nbody\r\n--boundary--");
        /** @var MultipartBodyPart[] $bodyParts */
        $bodyParts = $this->parser->readAsMultipart($this->request)->getParts();
        $this->assertCount(1, $bodyParts);
        $this->assertEquals('bar', $bodyParts[0]->getHeaders()->getFirst('Foo'));
        $this->assertEquals('blah', $bodyParts[0]->getHeaders()->getFirst('Baz'));
    }

    /**
     * Tests parsing a multipart request with headers extracts the headers
     */
    public function testParsingMultipartRequestWithHeadersExtractsBody() : void
    {
        $this->headers->add('Content-Type', 'multipart/mixed; boundary="boundary"');
        $this->body->expects($this->once())
            ->method('readAsString')
            ->willReturn("--boundary\r\nFoo: bar\r\nBaz: blah\r\n\r\nbody\r\n--boundary--");
        /** @var MultipartBodyPart[] $bodyParts */
        $bodyParts = $this->parser->readAsMultipart($this->request)->getParts();
        $this->assertCount(1, $bodyParts);
        $this->assertEquals('body', $bodyParts[0]->getBody()->readAsString());
    }

    /**
     * Tests that parsing a multipart request with a null body returns null
     */
    public function testParsingMultipartRequestWithNullBodyReturnsNull() : void
    {
        $request = $this->createMock(IHttpRequestMessage::class);
        $request->expects($this->any())
            ->method('getHeaders')
            ->willReturn($this->headers);
        $request->expects($this->any())
            ->method('getBody')
            ->willReturn(null);
        $this->headers->add('Content-Type', 'multipart/mixed');
        $this->assertNull($this->parser->readAsMultipart($request));
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
     * Tests parsing nested multipart bodies adds the raw child bodies to the parent's body
     */
    public function testParsingNestedMultipartBodiesAddsRawChildBodiesToParentsBody() : void
    {
        $this->headers->add('Content-Type', 'multipart/mixed; boundary="boundary1"');
        $bodyString = "--boundary1\r\n" .
            // First nested multipart body
            'Content-Type: multipart/mixed; boundary="boundary2"' .
            "\r\n" .
            "\r\n" .
            'body1' .
            "\r\n" .
            // Second part of the nested multipart body
            '--boundary2' .
            "\r\n" .
            'Content-Type: multipart/mixed; boundary="boundary3"' .
            "\r\n" .
            "\r\n" .
            'body2' .
            "\r\n" .
            '--boundary3--' .
            "\r\n" .
            '--boundary2--' .
            "\r\n" .
            '--boundary1--';
        $this->body->expects($this->once())
            ->method('readAsString')
            ->willReturn($bodyString);
        /** @var MultipartBodyPart[] $bodyParts */
        $bodyParts = $this->parser->readAsMultipart($this->request)->getParts();
        $this->assertCount(1, $bodyParts);
        $this->assertEquals(
            'multipart/mixed; boundary="boundary2"',
            $bodyParts[0]->getHeaders()->getFirst('Content-Type')
        );
        $expectedBodyString = 'body1' .
            "\r\n" .
            '--boundary2' .
            "\r\n" .
            'Content-Type: multipart/mixed; boundary="boundary3"' .
            "\r\n" .
            "\r\n" .
            'body2' .
            "\r\n" .
            '--boundary3--' .
            "\r\n" .
            '--boundary2--';
        $this->assertEquals($expectedBodyString, $bodyParts[0]->getBody()->readAsString());
    }

    /**
     * Tests parsing a non-multipart request as a multipart request throws an exception
     */
    public function testParsingNonMultipartRequestAsMultipartRequestThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->headers->add('Content-Type', 'text/plain');
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
