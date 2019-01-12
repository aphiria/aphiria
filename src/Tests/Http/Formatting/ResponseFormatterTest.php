<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting;

use InvalidArgumentException;
use Opulence\Net\Http\Formatting\ResponseFormatter;
use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\IHttpResponseMessage;
use Opulence\Net\Http\StringBody;
use Opulence\Net\Uri;
use PHPUnit\Framework\TestCase;

/**
 * Tests the HTTP response message formatter
 */
class ResponseFormatterTest extends TestCase
{
    /** @var ResponseFormatter The formatter to use in tests */
    private $formatter;
    /** @var IHttpResponseMessage|\PHPUnit_Framework_MockObject_MockObject The message to use in tests */
    private $response;
    /** @var HttpHeaders The HTTP headers to use in tests */
    private $headers;

    public function setUp(): void
    {
        $this->formatter = new ResponseFormatter();
        $this->headers = new HttpHeaders();
        $this->response = $this->createMock(IHttpResponseMessage::class);
        $this->response->expects($this->any())
            ->method('getHeaders')
            ->willReturn($this->headers);
    }

    public function testContentTypeHeaderAndBodyAreSetWhenWritingJson(): void
    {
        $this->response->expects($this->once())
            ->method('setBody')
            ->with($this->callback(function ($body) {
                return $body instanceof StringBody && $body->readAsString() === json_encode(['foo' => 'bar']);
            }));
        $this->formatter->writeJson($this->response, ['foo' => 'bar']);
        $this->assertEquals('application/json', $this->response->getHeaders()->getFirst('Content-Type'));
    }

    public function testRedirectingToUriSetsLocationHeaderAndStatusCode(): void
    {
        $this->response->expects($this->once())
            ->method('setStatusCode')
            ->with(301);
        $this->formatter->redirectToUri($this->response, 'http://foo.com', 301);
        $this->assertEquals('http://foo.com', $this->headers->getFirst('Location'));
    }

    public function testRedirectingToUriConvertsUriInstanceToStringAndSetsLocationHeaderAndStatusCode(): void
    {
        $this->response->expects($this->once())
            ->method('setStatusCode')
            ->with(301);
        $this->formatter->redirectToUri($this->response, new Uri('http://foo.com'), 301);
        $this->assertEquals('http://foo.com', $this->headers->getFirst('Location'));
    }

    public function testRedirectingToUriThatIsNotUriNorStringThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Uri must be instance of %s or string', Uri::class));
        $this->formatter->redirectToUri($this->response, [], 301);
    }
}
