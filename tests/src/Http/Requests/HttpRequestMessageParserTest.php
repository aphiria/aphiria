<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Requests;

use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\IHttpBody;
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
        $this->assertEquals('bar', $this->parser->parseFormInput($this->request)->get('foo'));
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
        $this->assertEquals(['foo' => 'bar'], $this->parser->parseFormInput($this->request)->getAll());
    }

    /**
     * Tests that parsing form input with non-form-URL-encoded bodies return an empty array
     */
    public function testParsingInputWithNonFormUrlEncodedBodyReturnsEmptyArray() : void
    {
        $this->headers->add('Content-Type', 'application/json');
        $this->assertEquals([], $this->parser->parseFormInput($this->request)->getAll());
    }

    /**
     * Tests that parsing an input with non-form-URL-encoded bodies return null
     */
    public function testParsingInputOnNonFormUrlEncodedBodyReturnsNull() : void
    {
        $this->headers->add('Content-Type', 'application/json');
        $this->assertNull($this->parser->parseFormInput($this->request)->get('foo'));
    }

    /**
     * Tests that parsing input with a null body will return null
     */
    public function testParsingInputWithNullBodyWillReturnNull() : void
    {
        $this->body = null;
        $this->headers->add('Content-Type', 'application/x-www-form-urlencoded');
        $this->assertEquals([], $this->parser->parseFormInput($this->request)->getAll());
        $this->assertNull($this->parser->parseFormInput($this->request)->get('foo'));
    }

    /**
     * Tests that parsing non-existent form input returns null
     */
    public function testParsingNonExistentFormInputReturnsNull() : void
    {
        $this->body->expects($this->once())
            ->method('readAsString')
            ->willReturn('foo=bar');
        $this->headers->add('Content-Type', 'application/x-www-form-urlencoded');
        $this->assertNull($this->parser->parseFormInput($this->request)->get('baz'));
    }

    /**
     * Tests that reading as JSON for a JSON request return the JSON-decoded body
     */
    public function testReadAsJsonForJsonRequestReturnsJsonDecodedBody() : void
    {
        $this->headers->add('Content-Type', 'application/json');
        $this->body->expects($this->once())
            ->method('readAsString')
            ->willReturn(json_encode(['foo' => 'bar']));
        $this->assertEquals(['foo' => 'bar'], $this->parser->parseJson($this->request));
    }

    /**
     * Tests that reading as JSON for a non-JSON request returns an empty array
     */
    public function testReadAsJsonForNonJsonRequestReturnsEmptyArray() : void
    {
        $this->headers->add('Content-Type', 'application/x-www-form-urlencoded');
        $this->assertEquals([], $this->parser->parseJson($this->request));
    }

    /**
     * Tests that reading as JSON with incorrectly-formatted JSON throws an exception
     */
    public function testReadAsJsonWithIncorrectlyFormattedJsonThrowsException() : void
    {
        $this->expectException(RuntimeException::class);
        $this->headers->add('Content-Type', 'application/json');
        $this->body->expects($this->once())
            ->method('readAsString')
            ->willReturn("\x0");
        $this->parser->parseJson($this->request);
    }
}
