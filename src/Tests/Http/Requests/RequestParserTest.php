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
use Opulence\Net\Http\Requests\RequestParser;
use RuntimeException;

/**
 * Tests the HTTP request message parser
 */
class RequestParserTest extends \PHPUnit\Framework\TestCase
{
    /** @var RequestParser The parser to use in tests */
    private $parser = null;
    /** @var HttpHeaders The headers to use in tests */
    private $headers = null;
    /** @var IHttpBody|\PHPUnit_Framework_MockObject_MockObject The body to use in tests */
    private $body = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->parser = new RequestParser();
        $this->headers = new HttpHeaders();
        $this->body = $this->createMock(IHttpBody::class);
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
        $this->assertEquals('bar', $this->parser->readAsFormInput($this->headers, $this->body)->get('foo'));
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
        $this->assertEquals('bar', $this->parser->readAsFormInput($this->headers, $this->body)->get('foo'));
    }

    /**
     * Tests that parsing form input with non-form-URL-encoded bodies throws an exception
     */
    public function testParsingInputWithNonFormUrlEncodedBodyThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->headers->add('Content-Type', 'application/json');
        $this->parser->readAsFormInput($this->headers, $this->body);
    }

    /**
     * Tests that parsing input with a null body throws an exception
     */
    public function testParsingInputWithNullBodyThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->headers->add('Content-Type', 'application/x-www-form-urlencoded');
        $this->parser->readAsFormInput($this->headers, null);
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
        $this->assertEquals(['foo' => 'bar'], $this->parser->readAsJson($this->headers, $this->body));
    }

    /**
     * Tests that parsing as JSON for a non-JSON request throws an exception
     */
    public function testParsingJsonForNonJsonRequestThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->headers->add('Content-Type', 'application/x-www-form-urlencoded');
        $this->parser->readAsJson($this->headers, $this->body);
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
        $this->parser->readAsJson($this->headers, $this->body);
    }

    /**
     * Tests that parsing JSON with a null body throws an exception
     */
    public function testParsingJsonWithNullBodyThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->headers->add('Content-Type', 'application/json');
        $this->parser->readAsJson($this->headers, null);
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
        $bodyParts = $this->parser->readAsMultipart($this->headers, $this->body);
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
        $bodyParts = $this->parser->readAsMultipart($this->headers, $this->body);
        $this->assertCount(1, $bodyParts);
        $this->assertEquals('body', $bodyParts[0]->getBody()->readAsString());
    }

    /**
     * Tests that parsing a multipart request with a null body throws an exception
     */
    public function testParsingMultipartRequestWithNullBodyThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->headers->add('Content-Type', 'multipart/mixed');
        $this->parser->readAsJson($this->headers, null);
    }

    /**
     * Tests parsing a multipart request without a boundary throws an exception
     */
    public function testParsingMultipartRequestWithoutBoundaryThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->headers->add('Content-Type', 'multipart/mixed');
        $this->parser->readAsMultipart($this->headers, $this->body);
    }

    /**
     * Tests parsing a non-multipart request as a multipart request throws an exception
     */
    public function testParsingNonMultipartRequestAsMultipartRequestThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->headers->add('Content-Type', 'text/plain');
        $this->parser->readAsMultipart($this->headers, $this->body);
    }
}
