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
     * Tests that getting form data with form-url-encoded bodies return the parsed form data
     */
    public function testGettingFormDataWithFormUrlEncodedBodyReturnsParsedFormData() : void
    {
        $this->body->expects($this->once())
            ->method('readAsString')
            ->willReturn('foo=bar');
        $this->headers->add('Content-Type', 'application/x-www-form-urlencoded');
        $this->assertEquals(['foo' => 'bar'], $this->parser->getFormData($this->request));
    }

    /**
     * Tests that getting form data with non-form-url-encoded bodies return an empty array
     */
    public function testGettingFormDataWithNonFormUrlEncodedBodyReturnsEmptyArray() : void
    {
        $this->headers->add('Content-Type', 'application/json');
        $this->assertEquals([], $this->parser->getFormData($this->request));
    }

    /**
     * Tests that getting an input with non-form-url-encoded bodies return null
     */
    public function testGettingInputOnNonFormUrlEncodedBodyReturnsNull() : void
    {
        $this->headers->add('Content-Type', 'application/json');
        $this->assertNull($this->parser->getInput($this->request, 'foo'));
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
        $this->assertEquals('bar', $this->parser->getInput($this->request, 'foo'));
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
        $this->assertNull($this->parser->getInput($this->request, 'baz'));
        $this->assertEquals('ahhh', $this->parser->getInput($this->request, 'baz', 'ahhh'));
    }
}
