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
use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\IHttpBody;
use Opulence\Net\Http\Requests\HttpRequestMessageParser;
use Opulence\Net\Http\Requests\IHttpRequestMessage;
use RuntimeException;

/**
 * Tests the HTTP request message parser
 */
class HttpRequestMessageParserTest extends \PHPUnit\Framework\TestCase
{
    /** @var HttpRequestMessageParser The parser to use in tests */
    private $parser = null;
    /** @var IHttpRequestMessage|\PHPUnit_Framework_MockObject_MockObject The request to use in tests */
    private $request = null;
    /** @var HttpHeaders The headers to use in tests */
    private $headers = null;
    /** @var IHttpBody|\PHPUnit_Framework_MockObject_MockObject The body to use in tests */
    private $body = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->parser = new HttpRequestMessageParser();
        $this->headers = new HttpHeaders();
        $this->body = $this->createMock(IHttpBody::class);
        $this->request = $this->createMock(IHttpRequestMessage::class);
        $this->request->expects($this->any())
            ->method('getHeaders')
            ->willReturn($this->headers);
        $this->request->expects($this->any())
            ->method('getBody')
            ->willReturn($this->body);
    }

    /**
     * Tests that parsing existing form input returns that input's value
     */
    public function testGettingExistingFormInputReturnsThatInputsValue() : void
    {
        $this->body->expects($this->once())
            ->method('readAsString')
            ->willReturn('foo=bar');
        $this->headers->add('Content-Type', 'application/x-www-form-urlencoded');
        $this->assertEquals('bar', $this->parser->readAsFormInput($this->request)->get('foo'));
    }

    /**
     * Tests that parsing form input with form-URL-encoded bodies return the parsed form data
     */
    public function testParsingInputWithFormUrlEncodedBodyReturnsParsedFormData() : void
    {
        $this->body->expects($this->once())
            ->method('readAsString')
            ->willReturn('foo=bar');
        $this->headers->add('Content-Type', 'application/x-www-form-urlencoded');
        $this->assertEquals('bar', $this->parser->readAsFormInput($this->request)->get('foo'));
    }

    /**
     * Tests that parsing form input with non-form-URL-encoded bodies return an empty array
     */
    public function testParsingInputWithNonFormUrlEncodedBodyReturnsEmptyArray() : void
    {
        $this->headers->add('Content-Type', 'application/json');
        $this->assertEquals([], $this->parser->readAsFormInput($this->request)->toArray());
    }

    /**
     * Tests that parsing an input with non-form-URL-encoded bodies return an empty dictionary
     */
    public function testParsingInputOnNonFormUrlEncodedBodyReturnsEmptyDictionary() : void
    {
        $this->headers->add('Content-Type', 'application/json');
        $this->assertEquals([], $this->parser->readAsFormInput($this->request)->toArray());
        $this->assertCount(0, $this->parser->readAsFormInput($this->request));
    }

    /**
     * Tests that parsing input with a null body will return an empty dictionary
     */
    public function testParsingInputWithNullBodyWillReturnEmptyDictionary() : void
    {
        $this->body = null;
        $this->headers->add('Content-Type', 'application/x-www-form-urlencoded');
        $this->assertEquals([], $this->parser->readAsFormInput($this->request)->toArray());
        $this->assertCount(0, $this->parser->readAsFormInput($this->request));
    }

    /**
     * Tests that parsing as JSON for a JSON request return the JSON-decoded body
     */
    public function testParsingJsonForJsonRequestReturnsJsonDecodedBody() : void
    {
        $this->headers->add('Content-Type', 'application/json');
        $this->body->expects($this->once())
            ->method('readAsString')
            ->willReturn(json_encode(['foo' => 'bar']));
        $this->assertEquals(['foo' => 'bar'], $this->parser->readAsJson($this->request));
    }

    /**
     * Tests that parsing as JSON for a non-JSON request returns an empty array
     */
    public function testParsingJsonForNonJsonRequestReturnsEmptyArray() : void
    {
        $this->headers->add('Content-Type', 'application/x-www-form-urlencoded');
        $this->assertEquals([], $this->parser->readAsJson($this->request));
    }

    /**
     * Tests that parsing as JSON with incorrectly-formatted JSON throws an exception
     */
    public function testParsingJsonWithIncorrectlyFormattedJsonThrowsException() : void
    {
        $this->expectException(RuntimeException::class);
        $this->headers->add('Content-Type', 'application/json');
        $this->body->expects($this->once())
            ->method('readAsString')
            ->willReturn("\x0");
        $this->parser->readAsJson($this->request);
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
        $bodyParts = $this->parser->readAsMultipart($this->request);
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
        $bodyParts = $this->parser->readAsMultipart($this->request);
        $this->assertCount(1, $bodyParts);
        $this->assertEquals('body', $bodyParts[0]->getBody()->readAsString());
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
     * Tests parsing a non-multipart request as a multipart request throws an exception
     */
    public function testParsingNonMultipartRequestAsMultipartRequestThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->headers->add('Content-Type', 'text/plain');
        $this->parser->readAsMultipart($this->request);
    }
}
