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
     * Tests that getting existing form input returns that input's value
     */
    public function testGettingExistingFormInputReturnsThatInputsValue() : void
    {
        $this->body->expects($this->once())
            ->method('readAsString')
            ->willReturn('foo=bar');
        $this->headers->add('Content-Type', 'application/x-www-form-urlencoded');
        $this->assertEquals('bar', $this->parser->getFormInput($this->request, 'foo'));
    }

    /**
     * Tests that getting form input with form-URL-encoded bodies return the parsed form data
     */
    public function testGettingFormInputWithFormUrlEncodedBodyReturnsParsedFormData() : void
    {
        $this->body->expects($this->once())
            ->method('readAsString')
            ->willReturn('foo=bar');
        $this->headers->add('Content-Type', 'application/x-www-form-urlencoded');
        $this->assertEquals(['foo' => 'bar'], $this->parser->getAllFormInput($this->request));
    }

    /**
     * Tests that getting form input with non-form-URL-encoded bodies return an empty array
     */
    public function testGettingFormInputWithNonFormUrlEncodedBodyReturnsEmptyArray() : void
    {
        $this->headers->add('Content-Type', 'application/json');
        $this->assertEquals([], $this->parser->getAllFormInput($this->request));
    }

    /**
     * Tests that getting form input with a null body will return an empty array
     */
    public function testGettingFormInputWithNullBodyWillReturnEmptyArray() : void
    {
        $this->body = null;
        $this->headers->add('Content-Type', 'application/x-www-form-urlencoded');
        $this->assertEquals([], $this->parser->getAllFormInput($this->request));
    }

    /**
     * Tests that getting an input with non-form-URL-encoded bodies return null
     */
    public function testGettingInputOnNonFormUrlEncodedBodyReturnsNull() : void
    {
        $this->headers->add('Content-Type', 'application/json');
        $this->assertNull($this->parser->getFormInput($this->request, 'foo'));
    }

    /**
     * Tests that getting input with a null body will return the default value
     */
    public function testGettingInputWithNullBodyWillReturnDefault() : void
    {
        $this->body = null;
        $this->headers->add('Content-Type', 'application/x-www-form-urlencoded');
        $this->assertNull($this->parser->getFormInput($this->request, 'foo'));
        $this->assertEquals('baz', $this->parser->getFormInput($this->request, 'foo', 'baz'));
    }

    /**
     * Tests that getting non-existent form input returns default value
     */
    public function testGettingNonExistentFormInputReturnsDefaultValue() : void
    {
        $this->body->expects($this->once())
            ->method('readAsString')
            ->willReturn('foo=bar');
        $this->headers->add('Content-Type', 'application/x-www-form-urlencoded');
        $this->assertNull($this->parser->getFormInput($this->request, 'baz'));
        $this->assertEquals('ahhh', $this->parser->getFormInput($this->request, 'baz', 'ahhh'));
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
        $this->assertEquals(['foo' => 'bar'], $this->parser->readAsJson($this->request));
    }

    /**
     * Tests that reading as JSON for a non-JSON request returns an empty array
     */
    public function testReadAsJsonForNonJsonRequestReturnsEmptyArray() : void
    {
        $this->headers->add('Content-Type', 'application/x-www-form-urlencoded');
        $this->assertEquals([], $this->parser->readAsJson($this->request));
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
        $this->parser->readAsJson($this->request);
    }
}
