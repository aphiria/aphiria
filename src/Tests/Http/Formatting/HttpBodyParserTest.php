<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting;

use Opulence\Collections\HashTable;
use Opulence\Net\Http\Formatting\HttpBodyParser;
use Opulence\Net\Http\IHttpBody;
use RuntimeException;

/**
 * Tests the HTTP body parser
 */
class HttpBodyParserTest extends \PHPUnit\Framework\TestCase
{
    /** @var HttpBodyParser The parser to use in tests */
    private $parser = null;
    /** @var IHttpBody|\PHPUnit_Framework_MockObject_MockObject The body to use in tests */
    private $body = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->parser = new HttpBodyParser();
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
        $this->assertEquals('bar', $this->parser->readAsFormInput($this->body)->get('foo'));
    }

    /**
     * Tests that getting the mime type returns the correct mime type
     */
    public function testGettingMimeTypeReturnsCorrectMimeType() : void
    {
        $this->body->expects($this->once())
            ->method('readAsString')
            ->willReturn('<?xml version="1.0"?><foo />');
        $this->assertEquals('application/xml', $this->parser->getMimeType($this->body));
    }

    /**
     * Tests that parsing form input with form-URL-encoded bodies return the parsed form data
     */
    public function testParsingInputWithFormUrlEncodedBodyReturnsParsedFormData() : void
    {
        $this->body->expects($this->once())
            ->method('readAsString')
            ->willReturn('foo=bar');
        $this->assertEquals('bar', $this->parser->readAsFormInput($this->body)->get('foo'));
    }

    /**
     * Tests that parsing input with a null body returns an empty dictionary
     */
    public function testParsingInputWithNullBodyReturnsEmptyDictionary() : void
    {
        $this->assertEquals(new HashTable(), $this->parser->readAsFormInput(null));
    }

    /**
     * Tests that parsing as JSON for a JSON request return the JSON-decoded body
     */
    public function testParsingJsonForJsonRequestReturnsJsonDecodedBody() : void
    {
        $this->body->expects($this->once())
            ->method('readAsString')
            ->willReturn(json_encode(['foo' => 'bar']));
        $this->assertEquals(['foo' => 'bar'], $this->parser->readAsJson($this->body));
    }

    /**
     * Tests that parsing as JSON with incorrectly-formatted JSON throws an exception
     */
    public function testParsingJsonWithIncorrectlyFormattedJsonThrowsException() : void
    {
        $this->expectException(RuntimeException::class);
        $this->body->expects($this->once())
            ->method('readAsString')
            ->willReturn("\x0");
        $this->parser->readAsJson($this->body);
    }

    /**
     * Tests that parsing JSON with a null body returns an empty array
     */
    public function testParsingJsonWithNullBodyReturnsEmptyArray() : void
    {
        $this->assertEquals([], $this->parser->readAsJson(null));
    }

    /**
     * Tests parsing a multipart request extracts the headers
     */
    public function testParsingMultipartRequestExtractsHeaders() : void
    {
        $this->body->expects($this->once())
            ->method('readAsString')
            ->willReturn("--boundary\r\nFoo: bar\r\nBaz: blah\r\n\r\nbody\r\n--boundary--");
        /** @var MultipartBodyPart[] $bodyParts */
        $bodyParts = $this->parser->readAsMultipart($this->body, 'boundary')->getParts();
        $this->assertCount(1, $bodyParts);
        $this->assertEquals('bar', $bodyParts[0]->getHeaders()->getFirst('Foo'));
        $this->assertEquals('blah', $bodyParts[0]->getHeaders()->getFirst('Baz'));
    }

    /**
     * Tests parsing a multipart request with headers extracts the headers
     */
    public function testParsingMultipartRequestWithHeadersExtractsBody() : void
    {
        $this->body->expects($this->once())
            ->method('readAsString')
            ->willReturn("--boundary\r\nFoo: bar\r\nBaz: blah\r\n\r\nbody\r\n--boundary--");
        /** @var MultipartBodyPart[] $bodyParts */
        $bodyParts = $this->parser->readAsMultipart($this->body, 'boundary')->getParts();
        $this->assertCount(1, $bodyParts);
        $this->assertEquals('body', $bodyParts[0]->getBody()->readAsString());
    }

    /**
     * Tests that parsing a multipart request with a null body returns null
     */
    public function testParsingMultipartRequestWithNullBodyReturnsNull() : void
    {
        $this->assertNull($this->parser->readAsMultipart(null, 'boundary'));
    }

    /**
     * Tests parsing nested multipart bodies adds the raw child bodies to the parent's body
     */
    public function testParsingNestedMultipartBodiesAddsRawChildBodiesToParentsBody() : void
    {
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
        $bodyParts = $this->parser->readAsMultipart($this->body, 'boundary1')->getParts();
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
}
