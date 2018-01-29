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
use Opulence\Net\Http\Formatting\ResponseFormatter;
use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\IHttpResponseMessage;
use Opulence\Net\Http\StringBody;
use Opulence\Net\Uri;

/**
 * Tests the HTTP response message formatter
 */
class ResponseFormatterTest extends \PHPUnit\Framework\TestCase
{
    /** @var ResponseFormatter The formatter to use in tests */
    private $formatter = null;
    /** @var IHttpResponseMessage|\PHPUnit_Framework_MockObject_MockObject The message to use in tests */
    private $response = null;
    /** @var HttpHeaders The HTTP headers to use in tests */
    private $headers = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->formatter = new ResponseFormatter();
        $this->headers = new HttpHeaders();
        $this->response = $this->createMock(IHttpResponseMessage::class);
        $this->response->expects($this->any())
            ->method('getHeaders')
            ->willReturn($this->headers);
    }

    /**
     * Tests that the content type header and body are set when writing JSON
     */
    public function testContentTypeHeaderAndBodyAreSetWhenWritingJson() : void
    {
        $this->response->expects($this->once())
            ->method('setBody')
            ->with($this->callback(function ($body) {
                return $body instanceof StringBody && $body->readAsString() === json_encode(['foo' => 'bar']);
            }));
        $this->formatter->writeJson($this->response, ['foo' => 'bar']);
        $this->assertEquals('application/json', $this->response->getHeaders()->getFirst('Content-Type'));
    }

    /**
     * Tests that redirecting to a URI sets the location header and sets the status code
     */
    public function testRedirectingToUriSetsLocationHeaderAndStatusCode() : void
    {
        $this->response->expects($this->once())
            ->method('setStatusCode')
            ->with(301);
        $this->formatter->redirectToUri($this->response, 'http://foo.com', 301);
        $this->assertEquals('http://foo.com', $this->headers->getFirst('Location'));
    }

    /**
     * Tests that redirecting to a URI converts a URI instance to a string and sets the location header and sets the status code
     */
    public function testRedirectingToUriConvertsUriInstanceToStringAndSetsLocationHeaderAndStatusCode() : void
    {
        $this->response->expects($this->once())
            ->method('setStatusCode')
            ->with(301);
        $this->formatter->redirectToUri($this->response, new Uri('http://foo.com'), 301);
        $this->assertEquals('http://foo.com', $this->headers->getFirst('Location'));
    }

    /**
     * Tests that redirecting to a URI that is not a string nor URI throws an exception
     */
    public function testRedirectingToUriThatIsNotUriNorStringThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->formatter->redirectToUri($this->response, [], 301);
    }
}
