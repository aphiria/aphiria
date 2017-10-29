<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http;

use Opulence\Collections\HashTable;
use Opulence\Net\Http\HttpBodyParser;
use Opulence\Net\Http\IHttpBody;
use RuntimeException;

/**
 * Tests the HTTP body parser
 */
class HttpBodyTest extends \PHPUnit\Framework\TestCase
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
}
